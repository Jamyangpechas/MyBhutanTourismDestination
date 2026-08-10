<?php
// Prevent any unexpected HTML error output from corrupting the JSON payload
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// 1. Absolute path resolution to prevent relative path require failures
$dbPath = realpath(__DIR__ . '/../../admin/config/db.php');
$modelPath = realpath(__DIR__ . '/../model/InquiryModel.php');

if (!$dbPath || !file_exists($dbPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration Error: Unable to locate db.php file.']);
    exit;
}

if (!$modelPath || !file_exists($modelPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration Error: Unable to locate InquiryModel.php file.']);
    exit;
}

require_once $dbPath;
require_once $modelPath;

// 2. Identify the PDO connection instance initialized in db.php
$dbConn = $pdo ?? $db ?? $conn ?? null;

if (!$dbConn || !($dbConn instanceof PDO)) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database Error: Active PDO connection instance ($pdo, $db, or $conn) not found in db.php.'
    ]);
    exit;
}

// Enable PDO exception mode so failed SQL queries throw catchable errors
$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    // 3. Parse and validate JSON input
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload received.']);
        exit;
    }

    // 4. Validate Required Fields
    if (empty($data['name']) || empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Validation Error: Please provide a valid full name and email address.'
        ]);
        exit;
    }

    // 5. Format & Sanitize Inputs
    $interestsList = is_array($data['interests'] ?? null) 
        ? implode(', ', array_map('htmlspecialchars', $data['interests'])) 
        : 'None';

    $sanitized = [
        'name'            => htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8'),
        'email'           => filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL),
        'nationality'     => htmlspecialchars(trim($data['nationality'] ?? 'international'), ENT_QUOTES, 'UTF-8'),
        'season'          => htmlspecialchars(trim($data['season'] ?? 'Any'), ENT_QUOTES, 'UTF-8'),
        'duration'        => max(1, (int)($data['duration'] ?? 1)),
        'adults'          => max(1, (int)($data['adults'] ?? 1)),
        'children'        => max(0, (int)($data['children'] ?? 0)),
        'infants'         => max(0, (int)($data['infants'] ?? 0)),
        'interests'       => $interestsList,
        'estimated_total' => htmlspecialchars($data['estimated_total'] ?? 'N/A', ENT_QUOTES, 'UTF-8')
    ];

    try {
        $inquiryModel = new InquiryModel($dbConn);
        
        if ($inquiryModel->createInquiry($sanitized)) {
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Inquiry submitted successfully.'
            ]);
        } else {
            throw new Exception('Database insertion returned false without an exception.');
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'SQL Error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Server Error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST with action=create.']);
}