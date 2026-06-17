<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM bets WHERE user_id = ? ORDER BY id DESC LIMIT 50");
$stmt->execute([$_SESSION['user_id']]);
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
