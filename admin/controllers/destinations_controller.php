<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/DestinationModel.php';

$action = $_GET['action'] ?? '';
$destModel = new DestinationModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Helper function to handle media upload
    function handleUpload($file, $existingPath = '', $existingType = 'image') {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/destinations/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid('dest_') . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $mediaType = in_array($ext, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';
                return [
                    'path' => '/uploads/destinations/' . $filename,
                    'type' => $mediaType
                ];
            }
        }
        return [
            'path' => $existingPath,
            'type' => $existingType
        ];
    }

    if ($action === 'add') {
        $media = handleUpload($_FILES['media_file'] ?? null);

        $_POST['media_path'] = $media['path'];
        $_POST['media_type'] = $media['type'];

        if ($destModel->addDestination($_POST)) {
            $_SESSION['flash_message'] = "Destination added successfully!";
            $_SESSION['flash_type']    = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to add destination.";
            $_SESSION['flash_type']    = "error";
        }
    } 
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $existingMedia = $_POST['existing_media'] ?? '';
        $existingType  = $_POST['existing_media_type'] ?? 'image';

        $media = handleUpload($_FILES['media_file'] ?? null, $existingMedia, $existingType);

        $_POST['media_path'] = $media['path'];
        $_POST['media_type'] = $media['type'];

        if ($destModel->updateDestination($id, $_POST)) {
            $_SESSION['flash_message'] = "Destination updated successfully!";
            $_SESSION['flash_type']    = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to update destination.";
            $_SESSION['flash_type']    = "error";
        }
    } 
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        
        if ($destModel->deleteDestination($id)) {
            $_SESSION['flash_message'] = "Destination deleted successfully!";
            $_SESSION['flash_type']    = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to delete destination.";
            $_SESSION['flash_type']    = "error";
        }
    }
}

header('Location: /admin/views/destinations.php');
exit;