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
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

$sql = "SELECT * FROM balance_logs WHERE user_id = ? ";
$params = [$userId];

if ($type !== 'all') {
    $sql .= " AND type = ? ";
    $params[] = $type;
}

$sql .= " ORDER BY id DESC LIMIT ?";
$params[] = $limit;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $logs]);
