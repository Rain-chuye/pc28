<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false]);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$data = json_decode(file_get_contents('php://input'), true);

$amount = (float)$data['amount'];
$count = (int)$data['count'];
$turnover = (float)$data['turnover'];
$msg = $data['message'];

try {
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO red_packets (total_amount, total_count, remaining_amount, remaining_count, min_turnover_req) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$amount, $count, $amount, $count, $turnover]);
    $pid = $db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO group_messages (user_id, message, type, packet_id) VALUES (1, ?, 'red_packet', ?)");
    $stmt->execute([$msg, $pid]);
    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
