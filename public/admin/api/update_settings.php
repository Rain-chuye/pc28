<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();

    if (isset($_GET['action']) && $_GET['action'] === 'clear_chat') {
        $db->exec("TRUNCATE TABLE group_messages");
        $db->exec("TRUNCATE TABLE chat_messages");
        echo json_encode(['success' => true, 'message' => '历史消息已全部清除']);
    } else {
        $data = json_decode(file_get_contents('php://input'), true);

        $db->beginTransaction();
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $value]);
        }
        $db->commit();

        echo json_encode(['success' => true, 'message' => '设置已更新']);
    }
} catch (Exception $e) {
    if(isset($db) && $db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
