<?php
header('Content-Type: application/json; charset=utf-8');

$adminDir = dirname(__DIR__);

require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/SdfModel.php';

$action = $_GET['action'] ?? '';

try {
    $model = new SdfModel($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // -----------------------------------------------------------
        // ACTION 1: UPDATE SECTION HEADINGS
        // -----------------------------------------------------------
        if ($action === 'update_headings') {
            $eyebrow      = trim($_POST['sdf_eyebrow'] ?? '');
            $intro        = trim($_POST['sdf_intro'] ?? '');
            $closingTitle = trim($_POST['sdf_closing_title'] ?? '');
            $closingDesc  = trim($_POST['sdf_closing_desc'] ?? '');

            if (empty($eyebrow) || empty($intro) || empty($closingTitle) || empty($closingDesc)) {
                throw new InvalidArgumentException('All heading fields are required.');
            }

            $model->updateHeadings([
                'eyebrow'       => $eyebrow,
                'intro'         => $intro,
                'closing_title' => $closingTitle,
                'closing_desc'  => $closingDesc
            ]);

            echo json_encode(['success' => true, 'message' => 'Section headings updated successfully!']);
            exit();
        }

        // -----------------------------------------------------------
        // ACTION 2: SAVE/UPDATE FEATURE CARD (WITH IMAGE UPLOAD)
        // -----------------------------------------------------------
        if ($action === 'save_card') {
            $cardId = !empty($_POST['card_id']) ? (int)$_POST['card_id'] : null;
            $title  = trim($_POST['title'] ?? '');
            $desc   = trim($_POST['desc'] ?? '');

            if (empty($title) || empty($desc)) {
                throw new InvalidArgumentException('Card title and body text are required.');
            }

            $imagePath = '';

            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                if (!in_array($mime, $allowedMimes)) {
                    throw new InvalidArgumentException('Invalid image format. Allowed: JPG, PNG, WEBP.');
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'sdf_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                $uploadDir = $adminDir . '/uploads/sdf/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $targetPath = $uploadDir . $filename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to upload image to server.');
                }

                $imagePath = '/admin/uploads/sdf/' . $filename;
            } elseif (!$cardId) {
                throw new InvalidArgumentException('Please upload a card image.');
            }

            $model->saveFeature([
                'id'    => $cardId,
                'title' => $title,
                'image' => $imagePath,
                'desc'  => $desc
            ]);

            echo json_encode([
                'success' => true,
                'message' => $cardId ? 'Feature card updated successfully!' : 'Feature card added successfully!'
            ]);
            exit();
        }

        // -----------------------------------------------------------
        // ACTION 3: DELETE CARD
        // -----------------------------------------------------------
        if ($action === 'delete_card') {
            $cardId = (int)($_POST['card_id'] ?? 0);
            if ($cardId <= 0) {
                throw new InvalidArgumentException('Invalid card ID provided.');
            }

            $card = $model->getFeatureById($cardId);
            if ($card && !empty($card['image'])) {
                $filePath = $adminDir . str_replace('/admin', '', $card['image']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $model->deleteFeature($cardId);

            echo json_encode(['success' => true, 'message' => 'Feature card removed successfully.']);
            exit();
        }
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action or request method.']);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
exit();