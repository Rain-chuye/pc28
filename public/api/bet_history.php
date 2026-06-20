<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    die;
}

$issue = $_GET['issue'] ?? '';
$userId = $_SESSION['user_id'];

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    if($issue) {
        $stmt = $db->prepare("SELECT SUM(win_amount) as total_win FROM bets WHERE user_id = ? AND issue_no = ? AND status = 1");
        $stmt->execute([$userId, $issue]);
        $totalWin = $stmt->fetchColumn();
        echo json_encode(['success' => true, 'total_win' => (float)($totalWin ?: 0)]);
    } else {
        $stmt = $db->prepare("SELECT * FROM bets WHERE user_id = ? ORDER BY id DESC LIMIT 50");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
