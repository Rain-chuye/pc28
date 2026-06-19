<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$action = $_GET['action'] ?? 'get_settings';

if($action === 'get_settings') {
    $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Default prefix if not set
    if(!isset($settings['agent_link_prefix'])) {
        $settings['agent_link_prefix'] = "http://" . $_SERVER['HTTP_HOST'] . "/register.html?ref=";
    }

    echo json_encode(['success' => true, 'data' => $settings]);
}
