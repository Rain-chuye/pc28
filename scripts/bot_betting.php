<?php
/**
 * 东爷国际 超真实机器人投注系统 (V22)
 * 特点：批量机器人、随机组合、独立房间
 */
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/Lottery.php';

$db = \App\Utils\DB::getInstance()->getConnection();

$playTypeMap = [
    'big' => '大', 'small' => '小', 'single' => '单', 'double' => '双',
    'big_single' => '大单', 'big_double' => '大双', 'small_single' => '小单', 'small_double' => '小双',
    'extreme_big' => '极大', 'extreme_small' => '极小', 'pair' => '对子', 'straight' => '顺子', 'triple' => '豹子'
];

$latest = \App\Model\Lottery::getLatest();
if (!$latest) die("No lottery data.");

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

// Correct Issue handling
$issueNumStr = $latest['issue_no'];
if (is_numeric($issueNumStr)) { $betIssue = (string)((int)$issueNumStr + 1); }
else { preg_match('/(\d+)$/', $issueNumStr, $matches); $betIssue = $matches ? substr($issueNumStr,0,-strlen($matches[1])).((int)$matches[1]+1) : $issueNumStr; }

// 1. Robot Summary (Announced at 20s mark)
if ($countdown > 15 && $countdown <= 20) {
    foreach(['high', 'low'] as $room) {
        $checkStmt = $db->prepare("SELECT id FROM group_messages WHERE room_type = ? AND message LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        $checkStmt->execute([$room, "%上期下注核对%"]);
        if (!$checkStmt->fetch()) {
            $stmt = $db->prepare("SELECT u.nickname, u.username, b.play_type, b.bet_amount FROM bets b JOIN users u ON b.user_id = u.id WHERE b.issue_no = ? AND b.odds_type = ? AND u.is_robot = 0 ORDER BY b.id ASC");
            $stmt->execute([$betIssue, $room]);
            $userAgg = [];
            foreach($stmt->fetchAll() as $ub) { $name = $ub['nickname'] ?: $ub['username']; $userAgg[$name][] = ($playTypeMap[$ub['play_type']] ?? $ub['play_type']).":".$ub['bet_amount']; }
            $lines = []; foreach($userAgg as $name => $bets) $lines[] = "{$name}: " . implode(", ", $bets);
            $msg = empty($lines) ? "📢 期号 [{$betIssue}] 封盘公告：\n本期暂无真实玩家投注。" : "📢 期号 [{$betIssue}] 上期下注核对：\n" . implode("\n", $lines);
            $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (0, ?, ?)")->execute([$room, $msg . "\n------------------\n已封盘，请等待开奖！"]);
        }
    }
}

// 2. High Realism Mass Robot Betting (Only if not closed)
if ($countdown > 30) {
    $robots = $db->query("SELECT id, nickname FROM users WHERE is_robot = 1 ORDER BY RAND() LIMIT 20")->fetchAll();
    if (empty($robots)) die("No robots found.");

    // Each issue, 1 to 5 different bots will bet
    $activeBotCount = mt_rand(1, 5);
    $selectedBots = array_slice($robots, 0, $activeBotCount);

    foreach($selectedBots as $bot) {
        $roomType = (mt_rand(0, 1) == 0 ? 'low' : 'high');

        // A bot can place 1 to 4 different play types in one message
        $betBatchSize = mt_rand(1, 4);
        $broadcastLines = [];

        for($j=0; $j<$betBatchSize; $j++) {
            $roll = mt_rand(1, 100);
            if($roll <= 50) $type = ['big','small','single','double'][mt_rand(0,3)];
            elseif($roll <= 75) $type = ['big_single','big_double','small_single','small_double'][mt_rand(0,3)];
            elseif($roll <= 85) $type = (string)mt_rand(0, 27);
            elseif($roll <= 95) $type = ['triple','straight','pair'][mt_rand(0,2)];
            else $type = ['extreme_big','extreme_small'][mt_rand(0,1)];

            $amount = [10, 20, 50, 100, 200, 300, 500, 1000][mt_rand(0, 7)];

            // Log to DB
            $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds_type, status) VALUES (?, ?, ?, ?, ?, 0)")
               ->execute([$bot['id'], $betIssue, $type, $amount, $roomType]);

            $cnType = $playTypeMap[$type] ?? $type;
            $broadcastLines[] = "【{$cnType}】{$amount}";
        }

        // Broadcast combined message for realism
        $chatMsg = "玩家 [{$bot['nickname']}] 第 {$betIssue} 期下注成功：\n" . implode("\n", $broadcastLines);
        $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (?, ?, ?)")->execute([$bot['id'], $roomType, $chatMsg]);
    }
}
