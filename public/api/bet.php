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
    $oddsType = isset($data['odds_type']) ? $data['odds_type'] : 'low';

    if ($amount <= 0) {
        echo json_encode(array('success' => false, 'message' => '金额无效'));
        die();
    }

    if (empty($issueNo)) {
        echo json_encode(array('success' => false, 'message' => '期号缺失'));
        die();
    }

    $db = \App\Utils\DB::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];

    // Anti-Double Betting Logic (不能倍投)
    // Find the last bet on the same play_type
    $stmt = $db->prepare("SELECT bet_amount FROM bets WHERE user_id = ? AND play_type = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId, $playType]);
    $lastBetAmount = $stmt->fetchColumn();

    if ($lastBetAmount && $amount > ($lastBetAmount * 1.5)) { // Allowing some variation but preventing doubling (2x)
        // If "No Double-up" means strictly no increase or just no 2x increase.
        // User said "不能倍投", which usually means no doubling (2x).
        // I'll set it to > 1.5x as a safeguard or just > last amount if strictly no increase.
        // Let's go with strictly no more than 1.5x of previous bet.
        echo json_encode(array('success' => false, 'message' => '为了风控安全，本次投注金额不能超过上次同玩法金额的1.5倍（禁止倍投）'));
        die();
    }

    try {
        if (\App\Model\Bet::place($userId, $issueNo, $playType, $amount, $oddsType)) {
            echo json_encode(array('success' => true, 'message' => '下注成功'));
        }
    } catch (\Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
}
