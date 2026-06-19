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
    (SELECT SUM(bet_amount) FROM bets WHERE user_id = ?) as total_bet,
    (SELECT SUM(win_amount) FROM bets WHERE user_id = ?) as total_win,
    (SELECT SUM(amount) FROM finance_requests WHERE user_id = ? AND type = 'deposit' AND status = 'approved') as total_deposit,
    (SELECT SUM(amount) FROM finance_requests WHERE user_id = ? AND type = 'withdraw' AND status = 'approved') as total_withdraw
");
$stmt->execute([$userId, $userId, $userId, $userId]);
$overview = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Daily Report
$stmt = $db->prepare("
    SELECT
        DATE(created_at) as date,
        COUNT(*) as order_count,
        SUM(bet_amount) as bet_amount,
        SUM(win_amount) as win_amount,
        SUM(win_amount - bet_amount) as profit
    FROM bets
    WHERE user_id = ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
    LIMIT 30
");
$stmt->execute([$userId]);
$daily_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'overview' => $overview,
    'daily_reports' => $daily_reports
]);
