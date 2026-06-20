<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';
header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$stmt = $db->query("SELECT f.*, u.username FROM finance_requests f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
