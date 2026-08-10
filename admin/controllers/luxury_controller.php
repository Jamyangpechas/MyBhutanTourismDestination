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
require_once $adminDir . '/models/LuxuryModel.php';

try {
    $model = new LuxuryModel($pdo);
    $current = $model->getSettings() ?: [];

    $handleUpload = function(string $fileKey, string $urlKey, string $existingVal) use ($adminDir) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES[$fileKey]['tmp_name'];
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath);

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
                'image/svg+xml' => 'svg'
            ];

            if (!array_key_exists($mimeType, $allowedMimes)) {
                throw new InvalidArgumentException("Invalid file format for {$fileKey}. Allowed: JPG, PNG, WEBP, GIF, SVG.");
            }

            $ext = $allowedMimes[$mimeType];
            $uploadDir = dirname($adminDir) . '/public/uploads/luxury/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;

            if (move_uploaded_file($tmpPath, $uploadDir . $newFileName)) {
                return '/public/uploads/luxury/' . $newFileName;
            }
        }

        if (!empty($_POST[$urlKey])) {
            return trim($_POST[$urlKey]);
        }

        return $existingVal;
    };

    $card1Image = $handleUpload('card_1_image', 'card_1_image_url', $current['card_1_image'] ?? '');
    $card2Image = $handleUpload('card_2_image', 'card_2_image_url', $current['card_2_image'] ?? '');

    $saveData = [
        'eyebrow'       => trim($_POST['eyebrow'] ?? ''),
        'title'         => trim($_POST['title'] ?? ''),
        'paragraph_1'   => trim($_POST['paragraph_1'] ?? ''),
        'paragraph_2'   => trim($_POST['paragraph_2'] ?? ''),
        'divider_quote' => trim($_POST['divider_quote'] ?? ''),
        'card_1_label'  => trim($_POST['card_1_label'] ?? ''),
        'card_1_image'  => $card1Image,
        'card_2_label'  => trim($_POST['card_2_label'] ?? ''),
        'card_2_image'  => $card2Image,
    ];

    if ($model->updateSettings($saveData)) {
        echo json_encode([
            'success' => true,
            'message' => 'Luxury Section updated successfully!'
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