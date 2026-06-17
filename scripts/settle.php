<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

function isTriple($numbers) {
    $nums = explode(',', $numbers);
    return count(array_unique($nums)) === 1;
}

function isPair($numbers) {
    $nums = explode(',', $numbers);
    return count(array_unique($nums)) === 2;
}

function isStraight($numbers) {
    $nums = explode(',', $numbers);
    sort($nums);
    return ($nums[1] == $nums[0] + 1 && $nums[2] == $nums[1] + 1) || (array_slice($nums, 0, 3) == [0, 8, 9]); // 089 is also straight in some variants, simplified here
}

function settle($db) {
    // Get all pending bets
    $stmt = $db->query("SELECT * FROM bets WHERE status = 0");
    $bets = $stmt->fetchAll();

    foreach ($bets as $bet) {
        // Get result for the issue
        $resStmt = $db->prepare("SELECT numbers, total_sum FROM lottery_results WHERE issue_no = ?");
        $resStmt->execute(array($bet['issue_no']));
        $result = $resStmt->fetch();

        if ($result) {
            $totalSum = (int)$result['total_sum'];
            $numbers = $result['numbers'];
            $isWin = false;
            $isReturn = false;

            // Logic for win check
            if ($bet['play_type'] == 'big' && $totalSum >= 14) $isWin = true;
            if ($bet['play_type'] == 'small' && $totalSum <= 13) $isWin = true;
            if ($bet['play_type'] == 'single' && $totalSum % 2 != 0) $isWin = true;
            if ($bet['play_type'] == 'double' && $totalSum % 2 == 0) $isWin = true;
            if ($bet['play_type'] == 'big_single' && $totalSum >= 14 && $totalSum % 2 != 0) $isWin = true;
            if ($bet['play_type'] == 'big_double' && $totalSum >= 14 && $totalSum % 2 == 0) $isWin = true;
            if ($bet['play_type'] == 'small_single' && $totalSum <= 13 && $totalSum % 2 != 0) $isWin = true;
            if ($bet['play_type'] == 'small_double' && $totalSum <= 13 && $totalSum % 2 == 0) $isWin = true;
            if ($bet['play_type'] == 'extreme_big' && $totalSum >= 22) $isWin = true;
            if ($bet['play_type'] == 'extreme_small' && $totalSum <= 5) $isWin = true;

            // Number bet
            if (is_numeric($bet['play_type']) && $totalSum == (int)$bet['play_type']) $isWin = true;

            // Handle special conditions for High/Low Odds
            // 低赔率：如果开出特码13和14就吃本不回本
            if ($bet['odds_type'] == 'low') {
                if ($totalSum == 13 || $totalSum == 14) {
                    $isWin = false; // Always lose on 13/14 for standard plays if not betting on 13/14 specifically
                    // Exception: if they specifically bet on the number 13 or 14
                    if ($bet['play_type'] == '13' || $bet['play_type'] == '14') {
                        $isWin = true;
                    }
                }
            }
            // 高赔率：如果开出豹子 对子 顺子 以及13和14就会回本 (Return principal)
            else if ($bet['odds_type'] == 'high') {
                if (!$isWin) {
                    if ($totalSum == 13 || $totalSum == 14 || isTriple($numbers) || isPair($numbers) || isStraight($numbers)) {
                        $isReturn = true;
                    }
                }
            }

            $status = $isWin ? 1 : ($isReturn ? 3 : 2);
            $winAmount = $isWin ? $bet['bet_amount'] * $bet['odds'] : ($isReturn ? $bet['bet_amount'] : 0);

            $updateStmt = $db->prepare("UPDATE bets SET status = ?, win_amount = ? WHERE id = ?");
            $updateStmt->execute(array($status, $winAmount, $bet['id']));

            if ($winAmount > 0) {
                $userStmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $userStmt->execute(array($winAmount, $bet['user_id']));
            }

            // Check for Agent turnover reward (Sub-agent reach 1000 turnover)
            checkAgentTurnoverReward($db, $bet['user_id']);
        }
    }
}

function checkAgentTurnoverReward($db, $userId) {
    // Check user info
    $stmt = $db->prepare("SELECT inviter_id, total_turnover FROM users WHERE id = ?");
    $stmt->execute(array($userId));
    $user = $stmt->fetch();

    if ($user && $user['inviter_id'] && $user['total_turnover'] >= 1000) {
        // Check if reward already given
        $checkStmt = $db->prepare("SELECT id FROM rebates WHERE sub_id = ? AND user_id = ? AND type = 'turnover'");
        $checkStmt->execute(array($userId, $user['inviter_id']));
        if (!$checkStmt->fetch()) {
            // Give 10 point reward to inviter
            $db->prepare("UPDATE users SET balance = balance + 10 WHERE id = ?")->execute(array($user['inviter_id']));
            $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'turnover', 10)")->execute(array($user['inviter_id'], $userId));
        }
    }
}

settle($db);
echo "Settlement completed.\n";
