<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    die;
}

$userId = $_SESSION['user_id'];
$db = \App\Utils\DB::getInstance()->getConnection();

try {
    // Last 7 days data
    $dates = [];
    $deposits = [];
    $turnovers = [];
    $withdrawals = [];

    for($i=6; $i>=0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('m-d', strtotime($date));

        $st = $db->prepare("SELECT SUM(amount) FROM finance_requests WHERE user_id = ? AND status = 'approved' AND type = 'deposit' AND DATE(created_at) = ?");
        $st->execute([$userId, $date]);
        $deposits[] = (float)($st->fetchColumn() ?: 0);

        $st = $db->prepare("SELECT SUM(bet_amount) FROM bets WHERE user_id = ? AND DATE(created_at) = ?");
        $st->execute([$userId, $date]);
        $turnovers[] = (float)($st->fetchColumn() ?: 0);

        $st = $db->prepare("SELECT SUM(amount) FROM finance_requests WHERE user_id = ? AND status = 'approved' AND type = 'withdraw' AND DATE(created_at) = ?");
        $st->execute([$userId, $date]);
        $withdrawals[] = (float)($st->fetchColumn() ?: 0);
    }

    // Play types distribution
    $st = $db->prepare("SELECT play_type, COUNT(*) as cnt FROM bets WHERE user_id = ? GROUP BY play_type");
    $st->execute([$userId]);
    $typesRaw = $st->fetchAll();
    $types = [];
    $totalCount = 0;
    foreach($typesRaw as $r) { $types[$r['play_type']] = (int)$r['cnt']; $totalCount += $r['cnt']; }

    // Today's summary
    $today = date('Y-m-d');
    $st = $db->prepare("SELECT SUM(bet_amount) FROM bets WHERE user_id = ? AND DATE(created_at) = ?");
    $st->execute([$userId, $today]);
    $todayTurnover = (float)$st->fetchColumn() ?: 0;

    $st = $db->prepare("SELECT SUM(win_amount - bet_amount) FROM bets WHERE user_id = ? AND DATE(created_at) = ? AND status != 0");
    $st->execute([$userId, $today]);
    $todayProfit = (float)$st->fetchColumn() ?: 0;

    echo json_encode(['success' => true, 'data' => [
        'dates' => $dates,
        'deposits' => $deposits,
        'turnovers' => $turnovers,
        'withdrawals' => $withdrawals,
        'types' => $types,
        'total_count' => $totalCount,
        'today_turnover' => $todayTurnover,
        'today_profit' => $todayProfit
    ]]);
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
