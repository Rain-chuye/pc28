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

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => '金额无效']);
        die;
    }

    $db->beginTransaction();
    try {
        // Check balance
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $balance = $stmt->fetchColumn();

        if ($balance < $amount) {
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
