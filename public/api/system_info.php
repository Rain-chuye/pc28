<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();

    // Ensure system_settings exists
    $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(50) NOT NULL,
        setting_value TEXT,
        PRIMARY KEY (id),
        UNIQUE KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $settings = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode(['success' => true, 'data' => $settings]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
