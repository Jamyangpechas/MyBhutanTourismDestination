<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/EventModel.php';

$action = $_GET['action'] ?? '';
$model  = new EventModel($pdo);

switch ($action) {
    case 'add':
        handleAdd($model);
        break;
    case 'edit':
        handleEdit($model);
        break;
    case 'delete':
        handleDelete($model);
        break;
    default:
        header('Location: /admin/views/events.php');
        exit;
}

/**
 * Formats start_date and end_date into a display string (e.g. "SEP 21 - 23" or "SEP 28 - OCT 02")
 */
function formatEventDate(string $startDateRaw, string $endDateRaw = ''): string {
    if (empty($startDateRaw)) {
        return '';
    }

    $startTimestamp = strtotime($startDateRaw);
    if (!$startTimestamp) {
        return '';
    }

    $startMonth = strtoupper(date('M', $startTimestamp));
    $startDay   = date('j', $startTimestamp);

    if (!empty($endDateRaw)) {
        $endTimestamp = strtotime($endDateRaw);
        if ($endTimestamp) {
            $endMonth = strtoupper(date('M', $endTimestamp));
            $endDay   = date('j', $endTimestamp);

            // Same month (e.g. SEP 21 - 23)
            if ($startMonth === $endMonth) {
                if ($startDay === $endDay) {
                    return "{$startMonth} {$startDay}";
                }
                return "{$startMonth} {$startDay} - {$endDay}";
            }

            // Cross-month (e.g. SEP 28 - OCT 02)
            return "{$startMonth} {$startDay} - {$endMonth} {$endDay}";
        }
    }

    // Single-day event (e.g. SEP 21)
    return "{$startMonth} {$startDay}";
}

function handleAdd(EventModel $model): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /admin/views/events.php');
        exit;
    }

    $mediaPath = '';
    $mediaType = 'image';

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $uploaded = processFileUpload($_FILES['media_file']);
        if ($uploaded) {
            $mediaPath = $uploaded['path'];
            $mediaType = $uploaded['type'];
        }
    }

    $startDate = trim($_POST['start_date'] ?? '');
    $endDate   = trim($_POST['end_date'] ?? '');

    $data = [
        'title'      => trim($_POST['title'] ?? ''),
        'season'     => trim($_POST['season'] ?? 'spring'),
        'category'   => trim($_POST['category'] ?? 'cat-tshechu'),
        'start_date' => $startDate,
        'end_date'   => $endDate,
        'date'       => formatEventDate($startDate, $endDate),
        'tag'        => trim($_POST['tag'] ?? ''),
        'location'   => trim($_POST['location'] ?? ''),
        'media'      => $mediaPath,
        'media_type' => $mediaType,
        'desc'       => trim($_POST['desc'] ?? ''),
        'highlights' => trim($_POST['highlights'] ?? '')
    ];

    $model->addEvent($data);
    header('Location: /admin/views/events.php?success=1');
    exit;
}

function handleEdit(EventModel $model): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /admin/views/events.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header('Location: /admin/views/events.php');
        exit;
    }

    $mediaPath = $_POST['existing_media'] ?? '';
    $mediaType = $_POST['existing_media_type'] ?? 'image';

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $uploaded = processFileUpload($_FILES['media_file']);
        if ($uploaded) {
            $mediaPath = $uploaded['path'];
            $mediaType = $uploaded['type'];
        }
    }

    $startDate = trim($_POST['start_date'] ?? '');
    $endDate   = trim($_POST['end_date'] ?? '');

    $data = [
        'title'      => trim($_POST['title'] ?? ''),
        'season'     => trim($_POST['season'] ?? 'spring'),
        'category'   => trim($_POST['category'] ?? 'cat-tshechu'),
        'start_date' => $startDate,
        'end_date'   => $endDate,
        'date'       => formatEventDate($startDate, $endDate),
        'tag'        => trim($_POST['tag'] ?? ''),
        'location'   => trim($_POST['location'] ?? ''),
        'media'      => $mediaPath,
        'media_type' => $mediaType,
        'desc'       => trim($_POST['desc'] ?? ''),
        'highlights' => trim($_POST['highlights'] ?? '')
    ];

    $model->updateEvent($id, $data);
    header('Location: /admin/views/events.php?success=1');
    exit;
}

function handleDelete(EventModel $model): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /admin/views/events.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $model->deleteEvent($id);
    }

    header('Location: /admin/views/events.php?success=1');
    exit;
}

function processFileUpload(array $file): ?array {
    $uploadDir = __DIR__ . '/../../public/uploads/events/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedMimes = [
        'image/jpeg' => 'image',
        'image/png'  => 'image',
        'image/webp' => 'image',
        'image/gif'  => 'image',
        'video/mp4'  => 'video',
        'video/webm' => 'video'
    ];

    if (!isset($allowedMimes[$mimeType])) {
        return null;
    }

    $extension  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName   = uniqid('event_', true) . '.' . strtolower($extension);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'path' => '/public/uploads/events/' . $fileName,
            'type' => $allowedMimes[$mimeType]
        ];
    }

    return null;
}