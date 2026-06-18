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

// 1. Betting Composition (Percentage)
$stmt = $db->prepare("SELECT play_type, SUM(bet_amount) as total FROM bets WHERE user_id = ? GROUP BY play_type");
$stmt->execute([$userId]);
$composition = $stmt->fetchAll();

// 2. Profit/Loss Stats & Volume
$stmt = $db->prepare("SELECT
    COUNT(DISTINCT issue_no) as total_issues,
    SUM(CASE WHEN type = 'bet' THEN ABS(amount) ELSE 0 END) as total_bet,
    SUM(CASE WHEN type = 'win' THEN amount ELSE 0 END) as total_win,
    SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_deposit,
    SUM(CASE WHEN type = 'withdraw' THEN ABS(amount) ELSE 0 END) as total_withdraw
FROM balance_logs WHERE user_id = ?");
$stmt->execute([$userId]);
$overview = $stmt->fetch();

// 3. Last 7 days daily profit/loss
$stmt = $db->prepare("SELECT
    DATE(created_at) as date,
    SUM(CASE WHEN type = 'win' THEN amount WHEN type = 'bet' THEN amount ELSE 0 END) as profit
FROM balance_logs
WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date ASC");
$stmt->execute([$userId]);
$daily = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'composition' => $composition,
    'overview' => $overview,
    'daily' => $daily
]);
