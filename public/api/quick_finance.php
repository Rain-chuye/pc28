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
    // 查分 (Score Check) - 从数据库获取实时最新余额
    $stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $balance = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'balance' => (float)$balance]);
} elseif ($action === 'return' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 回分 (Score Return) - 强制执行 50起提 + 4倍流水 规则
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = (float)($data['amount'] ?? 0);

    if ($amount < 50) {
        echo json_encode(['success' => false, 'message' => '回分金额最低 50 元起']);
        die;
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT balance, total_turnover, total_deposit, total_bonus FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) throw new Exception("用户不存在");

        // 4x Turnover Rule
        $requiredTurnover = ($user['total_deposit'] + $user['total_bonus']) * 4;
        if ($user['total_turnover'] < $requiredTurnover) {
            $diff = $requiredTurnover - $user['total_turnover'];
            throw new Exception("流水不足，还需 " . number_format($diff, 2) . " 积分流水方可回分");
        }

        if ($user['balance'] < $amount) {
            throw new Exception("积分余额不足");
        }

        // Apply deduction
        \App\Model\User::updateBalance($userId, -$amount, 'withdraw', '游戏快捷下分', $db);

        // Log request for admin
        $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount, status, admin_note) VALUES (?, 'withdraw', ?, 'pending', '用户端快捷回分')");
        $stmt->execute([$userId, $amount]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => '回分申请已提交，请联系客服确认']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
