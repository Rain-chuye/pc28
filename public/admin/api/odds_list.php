<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM odds_config ORDER BY id ASC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
