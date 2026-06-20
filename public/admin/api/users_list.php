<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, username, nickname, balance, qq_number, status, role FROM users WHERE is_robot = 0 ORDER BY id DESC");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
