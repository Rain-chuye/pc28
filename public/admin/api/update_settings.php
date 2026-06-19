<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';
header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$data = json_decode(file_get_contents('php://input'), true);

try {
    foreach($data as $key => $val) {
        $stmt = $db->prepare("REPLACE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $val]);
    }
    echo json_encode(['success' => true, 'message' => '系统设置已同步']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '保存失败']);
}
