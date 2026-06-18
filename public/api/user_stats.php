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

// 1. Overview
$stmt = $db->prepare("SELECT
    SUM(CASE WHEN type = 'bet' THEN ABS(amount) ELSE 0 END) as total_bet,
    SUM(CASE WHEN type = 'win' THEN amount ELSE 0 END) as total_win,
    SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_deposit,
    SUM(CASE WHEN type = 'withdraw' THEN ABS(amount) ELSE 0 END) as total_withdraw
FROM balance_logs WHERE user_id = ?");
$stmt->execute([$userId]);
$overview = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Daily Report (Last 30 days)
$stmt = $db->prepare("SELECT
    DATE(created_at) as date,
    COUNT(DISTINCT CASE WHEN type = 'bet' THEN issue_no END) as order_count,
    SUM(CASE WHEN type = 'bet' THEN ABS(amount) ELSE 0 END) as bet_amount,
    SUM(CASE WHEN type = 'win' THEN amount ELSE 0 END) as win_amount,
    SUM(CASE WHEN type IN ('bet', 'win') THEN amount ELSE 0 END) as profit
FROM balance_logs
WHERE user_id = ?
GROUP BY DATE(created_at)
ORDER BY date DESC
LIMIT 30");
$stmt->execute([$userId]);
$daily_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'overview' => $overview,
    'daily_reports' => $daily_reports
]);
