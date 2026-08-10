<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check for POST overflow (file exceeds post_max_size)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $_SESSION['error'] = "Uploaded file exceeds the maximum allowed server POST size limit.";
    header("Location: /admin/views/hero.php");
    exit();
}

// 2. Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php'; 
require_once __DIR__ . '/../models/HeroModel.php';

global $pdo;
$heroModel = new HeroModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eyebrow = trim($_POST['eyebrow'] ?? '');
    $title   = trim($_POST['title'] ?? '');

    if (empty($eyebrow) || empty($title)) {
        $_SESSION['error'] = "Eyebrow and Title fields are required.";
        header("Location: /admin/views/hero.php");
        exit();
    }

    // Disk Path: /admin/controllers/ -> /admin/ -> /admin/uploads/hero/
    $adminRoot = realpath(__DIR__ . '/../') ?: __DIR__ . '/..';
    $uploadDir = $adminRoot . '/uploads/hero/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $currentSettings = $heroModel->getHeroSettings();
    $mediaType       = $currentSettings['media_type'] ?? 'none';
    $mediaPath       = $currentSettings['media_path'] ?? null;
    $newFileUploaded = false;

    // Check individual upload size limits
    if ((isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_INI_SIZE) || 
        (isset($_FILES['hero_video']) && $_FILES['hero_video']['error'] === UPLOAD_ERR_INI_SIZE)) {
        $_SESSION['error'] = "Uploaded file exceeds the server upload_max_filesize limit.";
        header("Location: /admin/views/hero.php");
        exit();
    }

    $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
    $allowedVideos = ['video/mp4', 'video/webm'];

    // Handle Image Upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['hero_image']['tmp_name'];
        $mimeType = mime_content_type($fileTmp);
        $ext      = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));

        if (in_array($mimeType, $allowedImages) && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $fileName = 'hero_img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target   = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $target)) {
                $mediaType       = 'image';
                $mediaPath       = '/admin/uploads/hero/' . $fileName; // Absolute web URL
                $newFileUploaded = true;
            } else {
                $_SESSION['error'] = "Failed to move uploaded image to backend directory.";
                header("Location: /admin/views/hero.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid image format. Allowed formats: JPG, PNG, WEBP.";
            header("Location: /admin/views/hero.php");
            exit();
        }
    } 
    // Handle Video Upload
    elseif (isset($_FILES['hero_video']) && $_FILES['hero_video']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['hero_video']['tmp_name'];
        $mimeType = mime_content_type($fileTmp);
        $ext      = strtolower(pathinfo($_FILES['hero_video']['name'], PATHINFO_EXTENSION));

        if (in_array($mimeType, $allowedVideos) && in_array($ext, ['mp4', 'webm'])) {
            $fileName = 'hero_vid_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target   = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $target)) {
                $mediaType       = 'video';
                $mediaPath       = '/admin/uploads/hero/' . $fileName; // Absolute web URL
                $newFileUploaded = true;
            } else {
                $_SESSION['error'] = "Failed to move uploaded video to backend directory.";
                header("Location: /admin/views/hero.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid video format. Allowed formats: MP4, WEBM.";
            header("Location: /admin/views/hero.php");
            exit();
        }
    }

    // Delete previous physical file if replaced
    if ($newFileUploaded && !empty($currentSettings['media_path'])) {
        $projectRoot  = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../..';
        $oldCleanPath = '/' . ltrim($currentSettings['media_path'], '/');
        $oldFilePath  = $projectRoot . $oldCleanPath;
        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    // Update DB
    $updated = $heroModel->updateHeroSettings($eyebrow, $title, $mediaType, $mediaPath);

    if ($updated) {
        $heroModel->clearCache();
        $_SESSION['success'] = "Hero settings updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update database record.";
    }

    header("Location: /admin/views/hero.php");
    exit();
}