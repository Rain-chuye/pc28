<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$latest = \App\Model\Lottery::getLatest();

// Get settings
$settings = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$announcement = $settings['announcement'] ?? "欢迎来到 PC28 商业版，祝您游戏愉快！";
$drawInterval = (int)($settings['custom_draw_interval'] ?? 300);

if (!$latest) {
    $latest = [
        'issue_no' => '0',
        'numbers' => '0,0,0',
        'total_sum' => '0',
        'next_draw_at' => date('Y-m-d H:i:s', time() + $drawInterval)
    ];
}

$betIssueNo = (string)((int)$latest['issue_no'] + 1);

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

// If time passed, virtual countdown
if ($countdown < 0) {
    $countdown = $drawInterval + ($countdown % $drawInterval);
}

$isClosed = ($countdown <= 15);

// Odds
$odds = $db->query("SELECT play_type, odds_low, odds_high FROM odds_config")->fetchAll(PDO::FETCH_ASSOC);
$oddsMap = [];
foreach ($odds as $o) {
    $oddsMap[$o['play_type']] = [
        'low' => (float)$o['odds_low'],
        'high' => (float)$o['odds_high']
    ];
}

echo json_encode([
    'success' => true,
    'latest' => $latest,
    'bet_issue_no' => $betIssueNo,
    'countdown' => $countdown,
    'is_closed' => $isClosed,
    'announcement' => $announcement,
    'odds' => $oddsMap,
    'history' => \App\Model\Lottery::getHistory(20)
]);
