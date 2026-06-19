<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$latest = \App\Model\Lottery::getLatest();

if (!$latest) {
    $latest = ['issue_no' => '---', 'numbers' => '?,?,?', 'total_sum' => '?', 'next_draw_at' => date('Y-m-d H:i:s', time() + 215)];
}

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

// Business logic: 3:35 cycle (215 seconds)
// If countdown is negative, it means the scraper is lagging, but we show a waiting state
if ($countdown < 0) {
    $countdown = 0;
}

$isClosed = ($countdown <= 15);

echo json_encode([
    'success' => true,
    'latest' => $latest,
    'countdown' => $countdown,
    'is_closed' => $isClosed,
    'server_time' => date('Y-m-d H:i:s'),
    'history' => \App\Model\Lottery::getHistory(15)
]);
