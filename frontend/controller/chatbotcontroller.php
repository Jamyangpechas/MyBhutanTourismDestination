<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    // Navigate from frontend/controller up to frontend/model/
    $modelDir = dirname(__DIR__) . '/model';
    
    // Check both capital 'C' and lowercase 'c' filenames
    $chatbotPath = $modelDir . '/Chatbot.php';
    if (!file_exists($chatbotPath)) {
        $chatbotPath = $modelDir . '/chatbot.php';
    }

    if (!file_exists($chatbotPath)) {
        throw new RuntimeException("Chatbot model file not found at: " . $chatbotPath);
    }

    require_once $chatbotPath;

    $rawInput = file_get_contents('php://input');
    $input = json_decode((string)$rawInput, true);

    $userMessage = trim((string)($input['message'] ?? ''));
    $chatHistory = is_array($input['history'] ?? null) ? $input['history'] : [];

    if (empty($userMessage)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please enter a message.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $chatbot = new Chatbot();
    $result = $chatbot->ask($userMessage, $chatHistory);

    if (isset($result['success']) && $result['success'] === true) {
        $reply = (string)($result['message'] ?? '');

        // Standardize line breaks
        $reply = str_replace(["\r\n", "\r"], "\n", $reply);
        
        // Format bullet lists properly without breaking bold text
        $reply = preg_replace('/(^|[^\n])\n?\s*(?<!\*)\*(?!\*)\s+/', "$1\n\n* ", $reply);
        $reply = trim(preg_replace('/\n{3,}/', "\n\n", $reply));

        echo json_encode([
            'status'  => 'success',
            'reply'   => $reply,
            'message' => $reply // Backwards compatibility for app.js
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'status'  => 'error',
        'message' => $result['message'] ?? 'Unable to retrieve AI response.'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}