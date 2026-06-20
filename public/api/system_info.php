<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $settings = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode(['success' => true, 'data' => $settings]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
