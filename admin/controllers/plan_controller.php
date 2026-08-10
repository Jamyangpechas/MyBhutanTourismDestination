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
require_once $adminDir . '/models/PlanModel.php';

$action = $_GET['action'] ?? '';

try {
    $model = new PlanModel($pdo);

    if ($action === 'save_steps') {
        $rawTitles = $_POST['step_titles'] ?? [];
        $rawDescs  = $_POST['step_descriptions'] ?? [];
        
        if (!is_array($rawTitles) || empty($rawTitles)) {
            throw new InvalidArgumentException('At least one step title row is required.');
        }

        if ($model->updateSteps($rawTitles, $rawDescs)) {
            echo json_encode([
                'success' => true,
                'message' => 'Travel steps updated successfully!'
            ]);
        } else {
            throw new Exception('Failed to execute steps database update.');
        }
    } 
    elseif ($action === 'save_rates') {
        $allowedRates = [
            'sdf_intl', 'sdf_indian', 'visa_fee', 'monument_rate',
            'accommodation_rate', 'guide_rate', 'transport_rate', 'misc_rate'
        ];

        $saveData = [];
        foreach ($allowedRates as $rateKey) {
            if (!isset($_POST[$rateKey]) || $_POST[$rateKey] === '') {
                throw new InvalidArgumentException("Missing required rate field: {$rateKey}");
            }
            $saveData[$rateKey] = (float)$_POST[$rateKey];
        }

        if ($model->updateRates($saveData)) {
            echo json_encode([
                'success' => true,
                'message' => 'Calculator rates updated successfully!'
            ]);
        } else {
            throw new Exception('Failed to execute rates database update.');
        }
    } 
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or unspecified controller action.'
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