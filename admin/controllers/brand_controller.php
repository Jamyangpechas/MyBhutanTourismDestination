<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/BrandModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $brandModel = new BrandModel($pdo);
        $success = $brandModel->saveBrandData($_POST);

        if ($success) {
            $_SESSION['flash_message'] = "Brand Showcase settings updated successfully!";
            $_SESSION['flash_type']    = "success";
        } else {
            $_SESSION['flash_message'] = "Failed to update Brand Showcase settings.";
            $_SESSION['flash_type']    = "error";
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Error saving settings: " . $e->getMessage();
        $_SESSION['flash_type']    = "error";
    }

    header('Location: /admin/views/brand.php');
    exit;
}