<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Standard POST expected.'
    ]);
    exit();
}

$adminDir = dirname(__DIR__);

require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/PromoModel.php';

try {
    $model = new PromoModel($pdo);

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $btnText     = trim($_POST['btn_text'] ?? '');
    $btnUrl      = trim($_POST['btn_url'] ?? '');

    if (empty($title) || empty($description) || empty($btnText) || empty($btnUrl)) {
        throw new InvalidArgumentException('All fields (Title, Description, Button Text, and Button URL) are required.');
    }

    $saveData = [
        'title'       => $title,
        'description' => $description,
        'btn_text'    => $btnText,
        'btn_url'     => $btnUrl
    ];

    if ($model->updateSettings($saveData)) {
        echo json_encode([
            'success' => true,
            'message' => 'Promotional Banner settings updated successfully!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to execute database update query.'
        ]);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
exit();