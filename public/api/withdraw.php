<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];

    // 1. Minimum 50 withdrawal amount
    if ($amount < 50) {
        echo json_encode(['success' => false, 'message' => '最低提现金额为 50 元']);
        die;
    }

    $db->beginTransaction();
    try {
        // Fetch user data for turnover check
        $stmt = $db->prepare("SELECT balance, total_turnover, total_deposit, total_bonus FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) throw new Exception("用户不存在");

        // 2. 4x turnover requirement: Turnover >= (Deposit + Bonus) * 4
        $requiredTurnover = ($user['total_deposit'] + $user['total_bonus']) * 4;
        if ($user['total_turnover'] < $requiredTurnover) {
            $diff = $requiredTurnover - $user['total_turnover'];
            throw new Exception("流水不足，还需 ${diff} 流水方可提现 (需达到充值+福利的4倍)");
        }

        if ($user['balance'] < $amount) {
            throw new Exception("余额不足");
        }

        // Deduct balance
        $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);

        // Record request
        $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount) VALUES (?, 'withdraw', ?)");
        $stmt->execute([$userId, $amount]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => '提现申请已提交，请等待审核']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
