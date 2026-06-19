<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$latest = \App\Model\Lottery::getLatest();

if (!$latest) {
    $latest = [
        'issue_no' => '---',
        'numbers' => '?,?,?',
        'total_sum' => '?',
        'next_draw_at' => date('Y-m-d H:i:s', time() + 215)
    ];
}

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

// 保证倒计时逻辑
if ($countdown < 0) $countdown = 0;
$isClosed = ($countdown <= 15);

// Fetch Admin Announcement
$stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'announcement'");
$announcement = $stmt->fetchColumn() ?: "欢迎来到 PC28 加拿大至尊版！";

echo json_encode([
    'success' => true,
    'latest' => $latest,
    'countdown' => $countdown,
    'is_closed' => $isClosed,
    'announcement' => $announcement,
    'history' => \App\Model\Lottery::getHistory(20)
]);
