<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Bet.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Please login first'));
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $playType = isset($data['play_type']) ? $data['play_type'] : '';
    $amount = isset($data['amount']) ? $data['amount'] : 0;
    $issueNo = isset($data['issue_no']) ? $data['issue_no'] : '20240616-088';

    if ($amount <= 0) {
        echo json_encode(array('success' => false, 'message' => 'Invalid amount'));
        die();
    }

    try {
        if (\App\Model\Bet::place($_SESSION['user_id'], $issueNo, $playType, $amount)) {
            echo json_encode(array('success' => true, 'message' => 'Bet placed successfully'));
        }
    } catch (\Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
}
