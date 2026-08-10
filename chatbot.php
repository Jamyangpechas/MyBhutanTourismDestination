<?php

ini_set('display_errors', '0');
error_reporting(E_ALL);

ob_start();

// Safely load .env key-value pairs
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

// Fallback requires for manual file inclusion
require_once __DIR__ . '/frontend/model/Chatbot.php';

// Check for capitalized or lowercase controller file on disk
$controllerPath = __DIR__ . '/frontend/controller/ChatbotController.php';
if (!file_exists($controllerPath)) {
    $controllerPath = __DIR__ . '/frontend/controller/chatbotcontroller.php';
}
require_once $controllerPath;

use App\Controllers\ChatbotController;

try {
    $controller = new ChatbotController();
    $controller->handleRequest();
} catch (\Throwable $e) {
    if (ob_get_length()) ob_end_clean();

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => 'Internal Server Error: ' . $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine()
    ]);
}