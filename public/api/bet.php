<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Bet.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => '请先登录'));
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $playType = isset($data['play_type']) ? $data['play_type'] : '';
    $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
    $issueNo = isset($data['issue_no']) ? $data['issue_no'] : '';
    $oddsType = isset($data['odds_type']) ? $data['odds_type'] : 'low'; // 'low' or 'high'

    if ($amount <= 0) {
        echo json_encode(array('success' => false, 'message' => '金额无效'));
        die();
    }

    if (empty($issueNo)) {
        echo json_encode(array('success' => false, 'message' => '期号缺失'));
        die();
    }

    try {
        if (\App\Model\Bet::place($_SESSION['user_id'], $issueNo, $playType, $amount, $oddsType)) {
            echo json_encode(array('success' => true, 'message' => '下注成功'));
        }
    } catch (\Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
}
