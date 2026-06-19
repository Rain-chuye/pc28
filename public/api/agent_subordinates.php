<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

$agentId = $_SESSION['user_id'];
$db = \App\Utils\DB::getInstance()->getConnection();

try {
    // 1. Get subordinates
    $stmt = $db->prepare("
        SELECT
            u.id,
            u.username,
            u.total_turnover,
            u.total_deposit,
            u.created_at,
            (SELECT SUM(amount) FROM finance_requests WHERE user_id = u.id AND type = 'withdraw' AND status = 'approved') as total_withdraw,
            (SELECT SUM(amount) FROM rebates WHERE sub_id = u.id AND user_id = ?) as total_rebate_earned
        FROM users u
        WHERE u.inviter_id = ?
        ORDER BY u.id DESC
    ");
    $stmt->execute([$agentId, $agentId]);
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get aggregate stats for the agent
    $stmt = $db->prepare("SELECT SUM(amount) FROM rebates WHERE user_id = ?");
    $stmt->execute([$agentId]);
    $totalRebate = (float)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT SUM(amount) FROM rebates WHERE user_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$agentId]);
    $todayRebate = (float)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'subordinates' => $subs,
            'total_rebate' => $totalRebate,
            'today_rebate' => $todayRebate,
            'sub_count' => count($subs)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
