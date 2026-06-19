<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$latest = \App\Model\Lottery::getLatest();

if (!$latest) {
    $latest = [
        'issue_no' => '0',
        'numbers' => '0,0,0',
        'total_sum' => '0',
        'next_draw_at' => date('Y-m-d H:i:s', time() + 215)
    ];
}

// 核心修复：下注期号必须是当前已开奖期号 + 1
$betIssueNo = (string)((int)$latest['issue_no'] + 1);

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

if ($countdown < 0) $countdown = 0;
$isClosed = ($countdown <= 15);

// 获取后台远程配置
$stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'announcement'");
$announcement = $stmt->fetchColumn() ?: "欢迎来到 PC28 商业版，祝您游戏愉快！";

// 获取所有赔率配置
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
    'latest' => $latest,           // 这是上一期的开奖结果，用于前端显示
    'bet_issue_no' => $betIssueNo, // 这是当前正在接受投注的下一期期号
    'countdown' => $countdown,
    'is_closed' => $isClosed,
    'announcement' => $announcement,
    'odds' => $oddsMap,            // 新增赔率映射
    'history' => \App\Model\Lottery::getHistory(20)
]);
