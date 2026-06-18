<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'check';

if ($action === 'check') {
    // 查分 (Score Check)
    $user = \App\Model\User::getById($userId);
    echo json_encode(['success' => true, 'balance' => $user['balance']]);
} elseif ($action === 'return' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 回分 (Score Return / Fast Withdraw)
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = (float)($data['amount'] ?? 0);

    if ($amount < 10) {
        echo json_encode(['success' => false, 'message' => '最低下分金额为 10 元']);
        die;
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $currentBalance = $stmt->fetchColumn();

        if ($currentBalance < $amount) {
            throw new Exception("余额不足以回分");
        }

        // 扣除余额并记录
        \App\Model\User::updateBalance($userId, -$amount, 'withdraw', '快速回分申请', $db);

        // 自动创建财务请求
        $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount, status, admin_note) VALUES (?, 'withdraw', ?, 'pending', '用户游戏内快速回分')");
        $stmt->execute([$userId, $amount]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => '回分申请已提交，请联系客服处理']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
