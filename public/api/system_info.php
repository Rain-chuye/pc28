<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $settings = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Auto-update announcement with new bonus if needed
    $newAnnounce = "📢 限时福利：首充满 20 元送 21 积分！全网最高赔率 2.8，提现 5 分钟到账！";
    if(!isset($settings['announcement']) || strpos($settings['announcement'], '送 21') === false) {
        $st = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('announcement', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $st->execute([$newAnnounce, $newAnnounce]);
        $settings['announcement'] = $newAnnounce;
    }

    echo json_encode(['success' => true, 'data' => $settings]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
