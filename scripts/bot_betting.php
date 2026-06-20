<?php
/**
 * PC28 机器人投注 & 期号封盘公告系统 (V12)
 * 建议每 10 秒运行一次
 */
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/Lottery.php';

$db = \App\Utils\DB::getInstance()->getConnection();

// 1. Check for closure broadcast
$latest = \App\Model\Lottery::getLatest();
if ($latest) {
    $now = time();
    $nextDrawTs = strtotime($latest['next_draw_at']);
    $countdown = $nextDrawTs - $now;
    $betIssue = (string)((int)$latest['issue_no'] + 1);

    // Closure Summary Robot Message (Broadcast once between 15s and 20s)
    if ($countdown > 15 && $countdown <= 20) {
        $checkStmt = $db->prepare("SELECT id FROM group_messages WHERE message LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        $checkStmt->execute(["%期投注已截止%"]);
        if (!$checkStmt->fetch()) {
            $betsStmt = $db->prepare("SELECT COUNT(*) as cnt, SUM(bet_amount) as total FROM bets WHERE issue_no = ?");
            $betsStmt->execute([$betIssue]);
            $stats = $betsStmt->fetch();
            $count = $stats['cnt'] ?? 0;
            $total = $stats['total'] ?? 0;

            $msg = "📢 期号 [{$betIssue}] 封盘公告：\n------------------\n当前参与人数：{$count}\n总投注额：¥" . number_format($total, 2) . "\n------------------\n请耐心等待开奖，祝各位好运！🍀";
            $db->prepare("INSERT INTO group_messages (user_id, message) VALUES (0, ?)")->execute([$msg]);
        }
    }
}

// 2. Robot Random Betting (To keep group active)
$robots = $db->query("SELECT id, nickname FROM users WHERE is_robot = 1")->fetchAll();
if ($robots && mt_rand(1, 3) == 1 && $countdown > 20) { // 33% chance to bet if not closed
    $bot = $robots[array_rand($robots)];
    $types = ['big', 'small', 'single', 'double', 'big_single', 'big_double', 'small_single', 'small_double', 'straight', 'pair'];
    $type = $types[array_rand($types)];
    $amount = mt_rand(10, 500);
    $oddsType = (mt_rand(0, 1) == 0 ? 'low' : 'high');

    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds_type, status) VALUES (?, ?, ?, ?, ?, 0)")
       ->execute([$bot['id'], $betIssue, $type, $amount, $oddsType]);

    $cnMap = ['big'=>'大', 'small'=>'小', 'single'=>'单', 'double'=>'双', 'big_single'=>'大单', 'big_double'=>'大双', 'small_single'=>'小单', 'small_double'=>'小双', 'straight'=>'顺子', 'pair'=>'对子'];
    $cnType = $cnMap[$type] ?? $type;
    $chatMsg = "玩家 [{$bot['nickname']}] 下注成功：\n【{$cnType}】{$amount}";
    $db->prepare("INSERT INTO group_messages (user_id, message) VALUES (0, ?)")->execute([$chatMsg]);
}
