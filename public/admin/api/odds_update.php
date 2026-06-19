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

try {
    $stmt = $db->prepare("UPDATE odds_config SET odds_low = ?, odds_high = ? WHERE play_type = ?");
    $stmt->execute([$data['odds_low'], $data['odds_high'], $data['play_type']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
