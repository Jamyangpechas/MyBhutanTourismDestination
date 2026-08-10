<?php
declare(strict_types=1);

ob_start();
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

if (!isset($_FILES['passport_scan']) || $_FILES['passport_scan']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
    exit();
}

$fileTmpPath = $_FILES['passport_scan']['tmp_path'] ?? $_FILES['passport_scan']['tmp_name'];
$fileType    = $_FILES['passport_scan']['type'] ?? '';

// Structured default payload matching JS front-end schema
$parsedData = [
    'full_name'       => '',
    'passport_number' => '',
    'nationality'     => '',
    'passport_expiry' => ''
];

try {
    // Attempt CLI Tesseract scan if binary is present on host server
    $tesseractBin = trim((string) shell_exec('which tesseract 2>/dev/null'));

    if (!empty($tesseractBin) && file_exists($fileTmpPath)) {
        $outputFile = sys_get_temp_dir() . '/ocr_' . uniqid();
        $command    = sprintf('%s %s %s --oem 1 -l eng 2>&1', escapeshellarg($tesseractBin), escapeshellarg($fileTmpPath), escapeshellarg($outputFile));
        
        exec($command);
        $txtResultFile = $outputFile . '.txt';

        if (file_exists($txtResultFile)) {
            $rawText = file_get_contents($txtResultFile);
            @unlink($txtResultFile);

            if ($rawText) {
                // Parse MRZ line 1: Surname << Given Names
                if (preg_match('/P<[A-Z<]{3,}\b([A-Z<]+)/i', $rawText, $mrzMatches)) {
                    $cleanMrz = str_replace([' ', "\r", "\n"], '', $mrzMatches[0]);
                    $cleanMrz = preg_replace('/^P<[A-Z<]{3}/', '', $cleanMrz);
                    $parts    = array_filter(explode('<<', $cleanMrz));

                    if (count($parts) >= 2) {
                        $surname   = str_replace('<', ' ', $parts[0]);
                        $givenName = str_replace('<', ' ', $parts[1]);
                        $parsedData['full_name'] = trim($surname . ' ' . $givenName);
                    }
                }

                // Parse Passport Number
                if (preg_match('/\b([A-Z]\d{6,8})\b/', $rawText, $passMatches)) {
                    $parsedData['passport_number'] = strtoupper($passMatches[1]);
                }

                // Detect Nationality
                if (stripos($rawText, 'BHUTAN') !== false || stripos($rawText, 'BTN') !== false) {
                    $parsedData['nationality'] = 'BHUTANESE';
                }

                // Parse Passport Expiry (DD/MM/YYYY or YYYY-MM-DD)
                if (preg_match('/(\d{2})[\s.\/-](\d{2})[\s.\/-](20\d{2})/', $rawText, $expMatches)) {
                    $parsedData['passport_expiry'] = sprintf('%s-%s-%s', $expMatches[3], $expMatches[2], $expMatches[1]);
                }
            }
        }
    }
} catch (Throwable $e) {
    // Fallback quietly if CLI OCR process fails
    error_log('Passport OCR Server Exception: ' . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'data'    => $parsedData
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

exit();