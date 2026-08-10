<?php
declare(strict_types=1);

// Disable inline HTML error output to protect JSON serialization
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Start buffering output immediately
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 1. Point up two levels to admin/config/db.php
    require_once __DIR__ . '/../../admin/config/db.php';

    // 2. Point up one level to frontend/model/SeriesDepartureModel.php
    require_once __DIR__ . '/../model/SeriesDepartureModel.php';

    // Verify $pdo connection object exists
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection failed or $pdo instance missing.');
    }

    // Discard any output generated during require files (notices, white spaces)
    if (ob_get_length()) {
        ob_clean();
    }
    
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    $departureId    = (int)($_POST['departure_id'] ?? 0);
    $customerEmail  = filter_var(trim($_POST['customer_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $seatsRequested = (int)($_POST['cust_seats'] ?? $_POST['seats'] ?? 0);
    $pricePerSeat   = (float)($_POST['price_per_seat'] ?? 0.0);
    
    // Extract multi-passenger array sent from frontend form
    $passengersInput = $_POST['passengers'] ?? [];

    // Validation checks: departure ID, valid lead email, positive seat/price count, and non-empty passengers array
    if ($departureId <= 0 || !$customerEmail || $seatsRequested < 1 || $pricePerSeat <= 0 || empty($passengersInput) || !is_array($passengersInput)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please fill in all required passenger details accurately.'
        ]);
        exit();
    }

    $processedPassengers = [];
    $allowedMimeTypes    = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $maxFileSize         = 8 * 1024 * 1024; // 8MB limit per passport scan

    foreach ($passengersInput as $index => $passenger) {
        $fileData = null;
        $mimeType = null;

        // Check if a file was uploaded for this specific passenger index
        if (
            isset($_FILES['passengers']['tmp_name'][$index]['file']) && 
            $_FILES['passengers']['error'][$index]['file'] === UPLOAD_ERR_OK &&
            is_uploaded_file($_FILES['passengers']['tmp_name'][$index]['file'])
        ) {
            $tmpFilePath = $_FILES['passengers']['tmp_name'][$index]['file'];
            $fileSize     = $_FILES['passengers']['size'][$index]['file'] ?? 0;

            // Validate File Size
            if ($fileSize > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => "Passport file for Passenger " . ($index + 1) . " exceeds the maximum allowed size of 8MB."
                ]);
                exit();
            }

            // Detect MIME type reliably on the server side
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $detected = finfo_file($finfo, $tmpFilePath);
            finfo_close($finfo);

            if ($detected && in_array($detected, $allowedMimeTypes, true)) {
                $fileData = file_get_contents($tmpFilePath);
                $mimeType = $detected;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid file format for Passenger " . ($index + 1) . ". Only JPG, PNG, and PDF files are permitted."
                ]);
                exit();
            }
        }

        $processedPassengers[] = [
            'name'               => trim($passenger['name'] ?? ''),
            'passport'           => trim($passenger['passport'] ?? ''),
            'nationality'        => trim($passenger['nationality'] ?? ''),
            'expiry'             => trim($passenger['expiry'] ?? ''),
            'is_autofilled'      => isset($passenger['is_autofilled']) ? (int)$passenger['is_autofilled'] : 0,
            'passport_scan_data' => $fileData,
            'passport_mime_type' => $mimeType
        ];
    }

    $model  = new SeriesDepartureModel($pdo);
    $result = $model->processBooking(
        $departureId, 
        $customerEmail, 
        $seatsRequested, 
        $pricePerSeat,
        $processedPassengers
    );

    echo json_encode($result);

} catch (Throwable $e) {
    // Clean any partial HTML or error strings from the buffer
    if (ob_get_length()) {
        ob_clean();
    }

    error_log("book-series.php Controller Error: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);

    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred during reservation: ' . $e->getMessage()
    ]);
}
exit();