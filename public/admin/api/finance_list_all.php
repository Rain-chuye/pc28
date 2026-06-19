<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false]);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$stmt = $db->query("SELECT f.*, u.username FROM finance_requests f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
