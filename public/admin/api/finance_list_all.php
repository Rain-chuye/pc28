<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();

$stmt = $db->query("SELECT fr.*, u.username FROM finance_requests fr JOIN users u ON fr.user_id = u.id ORDER BY fr.id DESC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
