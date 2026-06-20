<?php
/**
 * PC28 机器人投注 & 期号封盘公告系统 (V16)
 * 建议每 5-10 秒运行一次
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
if ($latest) {
    $now = time();
    $nextDrawTs = strtotime($latest['next_draw_at']);
    $countdown = $nextDrawTs - $now;

    // Improved Issue Number Handling
    $issueNumStr = $latest['issue_no'];
    if (is_numeric($issueNumStr)) {
        $betIssue = (string)((int)$issueNumStr + 1);
    } else {
        preg_match('/(\d+)$/', $issueNumStr, $matches);
        if ($matches) {
            $prefix = substr($issueNumStr, 0, -strlen($matches[1]));
            $betIssue = $prefix . (string)((int)$matches[1] + 1);
        } else {
            $betIssue = $issueNumStr;
        }
    }

    // 1. Closure Summary (Verification) - Runs for both rooms independently
    if ($countdown > 10 && $countdown <= 20) {
        foreach(['high', 'low'] as $room) {
            $checkStmt = $db->prepare("SELECT id FROM group_messages WHERE room_type = ? AND message LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
            $checkStmt->execute([$room, "%上期下注核对%"]);

            if (!$checkStmt->fetch()) {
                $stmt = $db->prepare("SELECT u.nickname, u.username, b.play_type, b.bet_amount
                                     FROM bets b
                                     JOIN users u ON b.user_id = u.id
                                     WHERE b.issue_no = ? AND b.odds_type = ? AND u.is_robot = 0
                                     ORDER BY b.id ASC");
                $stmt->execute([$betIssue, $room]);
                $userBets = $stmt->fetchAll();

                $userAgg = [];
                foreach($userBets as $ub) {
                    $name = $ub['nickname'] ?: $ub['username'];
                    $cnType = $playTypeMap[$ub['play_type']] ?? $ub['play_type'];
                    $userAgg[$name][] = "{$cnType}:{$ub['bet_amount']}";
                }

                $summaryLines = [];
                foreach($userAgg as $name => $bets) $summaryLines[] = "{$name}: " . implode(", ", $bets);

                if (!empty($summaryLines)) {
                    $msg = "📢 期号 [{$betIssue}] 上期下注核对：\n" . implode("\n", $summaryLines) . "\n------------------\n已封盘，请等待开奖！";
                } else {
                    $msg = "📢 期号 [{$betIssue}] 封盘公告：\n本期无真实玩家投注。\n------------------\n已封盘，请等待开奖！";
                }

                $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (0, ?, ?)")->execute([$room, $msg]);
            }
        }
    }
}

// 2. Robot Random Betting (Random Room)
$robots = $db->query("SELECT id, nickname FROM users WHERE is_robot = 1")->fetchAll();
if ($robots && mt_rand(1, 2) == 1 && $countdown > 20) {
    $bot = $robots[array_rand($robots)];
    $types = array_keys($playTypeMap);
    $type = $types[array_rand($types)];
    $amount = mt_rand(10, 500);
    $roomType = (mt_rand(0, 1) == 0 ? 'low' : 'high');

    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds_type, status) VALUES (?, ?, ?, ?, ?, 0)")
       ->execute([$bot['id'], $betIssue, $type, $amount, $roomType]);

    $cnType = $playTypeMap[$type] ?? $type;
    $chatMsg = "玩家 [{$bot['nickname']}] 下注成功：\n【{$cnType}】{$amount}";
    $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (0, ?, ?)")->execute([$roomType, $chatMsg]);
}
