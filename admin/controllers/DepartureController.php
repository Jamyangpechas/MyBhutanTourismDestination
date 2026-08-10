<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load DB connection and Departure Model
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Departure.php';

class DepartureController {
    private $departureModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->departureModel = new Departure($pdo);
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            // Return JSON 401 if requested via AJAX, otherwise redirect
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized session.']);
                exit();
            }
            header("Location: /admin/auth/login.php");
            exit();
        }

        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'store':
                $this->store();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'update_payment':
                $this->updatePaymentStatus();
                break;
            case 'download_passport':
                $this->downloadPassport();
                break;
            default:
                $this->index();
                break;
        }
    }

    private function index() {
        $departures = $this->departureModel->getAll();
        $editDeparture = null;

        if (isset($_GET['edit_id']) && (int)$_GET['edit_id'] > 0) {
            $editDeparture = $this->departureModel->getById((int)$_GET['edit_id']);
        }

        // Render view file
        include __DIR__ . '/../views/seriesbooking.php';
    }

    private function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeInput($_POST);

            if ($this->validate($data)) {
                if ($this->departureModel->create($data)) {
                    $_SESSION['flash_success'] = "Departure created successfully!";
                } else {
                    $_SESSION['flash_error'] = "Failed to save departure to database.";
                }
            }
        }
        header("Location: DepartureController.php");
        exit();
    }

    private function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $data = $this->sanitizeInput($_POST);

            if ($this->validate($data)) {
                if ($this->departureModel->update($id, $data)) {
                    $_SESSION['flash_success'] = "Departure updated successfully!";
                } else {
                    $_SESSION['flash_error'] = "Failed to update departure in database.";
                }
            }
        }
        header("Location: DepartureController.php");
        exit();
    }

    private function delete() {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($this->departureModel->delete($id)) {
                $_SESSION['flash_success'] = "Departure deleted successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to delete departure.";
            }
        }
        header("Location: DepartureController.php");
        exit();
    }

    /**
     * Manually update booking payment status via AJAX JSON (from view modal) or POST form submission
     */
    private function updatePaymentStatus() {
        $bookingId = null;
        $status    = null;

        // Parse JSON input if sent via Fetch/AJAX
        if ($this->isJsonRequest()) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $bookingId = filter_var($jsonInput['booking_id'] ?? null, FILTER_VALIDATE_INT);
            $status    = trim($jsonInput['status'] ?? '');
        } else {
            // Standard form submit
            $bookingId = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
            $status    = trim($_POST['status'] ?? '');
        }

        $allowedStatuses = ['pending', 'paid', 'cancelled'];

        if (!$bookingId || !in_array($status, $allowedStatuses, true)) {
            $this->respondStatus(false, 'Invalid booking parameters.');
            return;
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Fetch departure ID tied to this booking
            $getDep = $this->pdo->prepare("SELECT departure_id FROM b2c_bookings WHERE id = ?");
            $getDep->execute([$bookingId]);
            $depId = (int)$getDep->fetchColumn();

            if (!$depId) {
                $this->pdo->rollBack();
                $this->respondStatus(false, 'Booking record not found.');
                return;
            }

            // 2. Update payment status
            $stmt = $this->pdo->prepare("UPDATE b2c_bookings SET payment_status = ? WHERE id = ?");
            $stmt->execute([$status, $bookingId]);

            // 3. Resync seats in the departures table immediately
            $syncStmt = $this->pdo->prepare("
                UPDATE departures d
                SET booked_seats = COALESCE((
                    SELECT SUM(seats_booked) 
                    FROM b2c_bookings 
                    WHERE departure_id = ? AND payment_status != 'cancelled'
                ), 0)
                WHERE d.id = ?
            ");
            $syncStmt->execute([$depId, $depId]);

            $this->pdo->commit();
            $this->respondStatus(true, "Payment status updated to " . ucfirst($status) . ".");

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DepartureController::updatePaymentStatus Error: " . $e->getMessage());
            $this->respondStatus(false, "Database update failed.");
        }
    }

    /**
     * Streams passport file directly from the database LONGBLOB in `booking_passengers`
     */
    private function downloadPassport() {
        $bookingId = filter_var($_GET['booking_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$bookingId) {
            http_response_code(400);
            die("Invalid booking request.");
        }

        try {
            // Query BLOB binary data directly from booking_passengers
            $stmt = $this->pdo->prepare("
                SELECT passport_scan_data, passport_mime_type, full_name 
                FROM booking_passengers 
                WHERE booking_id = ? AND passport_scan_data IS NOT NULL 
                LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $passenger = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$passenger || empty($passenger['passport_scan_data'])) {
                http_response_code(404);
                die("Passport record not found for this booking.");
            }

            $mimeType = !empty($passenger['passport_mime_type']) 
                ? $passenger['passport_mime_type'] 
                : 'application/octet-stream';

            // Determine file extension based on MIME type
            $extension = ($mimeType === 'application/pdf') ? 'pdf' : 'jpg';
            $safeName  = preg_replace('/[^a-zA-Z0-9_]/', '_', $passenger['full_name'] ?: 'passenger');
            $filename  = 'passport_' . $safeName . '.' . $extension;

            // Send binary response headers
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            echo $passenger['passport_scan_data'];
            exit();

        } catch (PDOException $e) {
            error_log("DepartureController::downloadPassport Error: " . $e->getMessage());
            http_response_code(500);
            die("Error processing passport document.");
        }
    }

    /**
     * Check if current request expects or sent JSON headers
     */
    private function isJsonRequest(): bool {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Helper to return standard JSON or standard Redirect flash messages
     */
    private function respondStatus(bool $success, string $message): void {
        if ($this->isJsonRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit();
        }

        if ($success) {
            $_SESSION['flash_success'] = $message;
        } else {
            $_SESSION['flash_error'] = $message;
        }

        header("Location: DepartureController.php");
        exit();
    }

    private function sanitizeInput($input) {
        return [
            'title'          => trim($input['title'] ?? ''),
            'description'    => trim($input['description'] ?? ''),
            'start_date'     => $input['start_date'] ?? '',
            'end_date'       => $input['end_date'] ?? '',
            'total_capacity' => (int)($input['total_capacity'] ?? 12),
            'min_passengers' => (int)($input['min_passengers'] ?? 4),
            'base_price'     => (float)($input['base_price'] ?? 0),
            'booked_seats'   => (int)($input['booked_seats'] ?? 0)
        ];
    }

    private function validate($data) {
        if (empty($data['title'])) {
            $_SESSION['flash_error'] = "Please provide a valid tour title.";
            return false;
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            $_SESSION['flash_error'] = "Start date and end date are required.";
            return false;
        }

        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $_SESSION['flash_error'] = "End date must be after start date.";
            return false;
        }

        if ($data['base_price'] <= 0) {
            $_SESSION['flash_error'] = "Base price must be greater than 0.";
            return false;
        }

        return true;
    }
}

// Instantiate and process
$controller = new DepartureController($pdo);
$controller->handleRequest();