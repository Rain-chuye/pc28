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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$data['amount'];

    if ($amount < 50) {
        echo json_encode(['success' => false, 'message' => '最低提现金额为 50 元']);
        die;
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT balance, total_turnover, total_deposit, total_bonus FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) throw new Exception("用户不存在");

        $requiredTurnover = ($user['total_deposit'] + $user['total_bonus']) * 4;
        if ($user['total_turnover'] < $requiredTurnover) {
            $diff = $requiredTurnover - $user['total_turnover'];
            throw new Exception("流水不足，还需 " . number_format($diff, 2) . " 流水方可提现");
        }

        if ($user['balance'] < $amount) {
            throw new Exception("余额不足");
        }

        \App\Model\User::updateBalance($userId, -$amount, 'withdraw', '申请提现扣款', $db);

        $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount, admin_note) VALUES (?, 'withdraw', ?, ?)");
        $stmt->execute([$userId, $amount, isset($data['note']) ? $data['note'] : '']);

        $db->commit();
        echo json_encode(['success' => true, 'message' => '提现申请已提交，请等待审核']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
