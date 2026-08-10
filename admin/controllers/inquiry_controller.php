<?php
// Prevent PHP warnings or notices from leaking HTML into JSON output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure administrative access
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$adminDir = dirname(__DIR__);
require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/InquiryModel.php';

$model = new InquiryModel($pdo);
$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action endpoint'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'update_status') {
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            if ($id <= 0 || empty($status)) {
                throw new Exception('Invalid inquiry parameters.');
            }

            if ($model->updateStatus($id, $status)) {
                $response = [
                    'success' => true,
                    'metrics' => $model->getMetrics()
                ];
            } else {
                throw new Exception('Failed to update status.');
            }
        } elseif ($action === 'delete_inquiry') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid inquiry ID.');
            }

            if ($model->deleteInquiry($id)) {
                $response = [
                    'success' => true,
                    'metrics' => $model->getMetrics()
                ];
            } else {
                throw new Exception('Failed to delete inquiry.');
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

ob_clean();
echo json_encode($response);
exit();