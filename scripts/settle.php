<?php
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/User.php';

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
    return ($nums[1] == $nums[0] + 1 && $nums[2] == $nums[1] + 1) || (array_slice($nums, 0, 3) == [0, 8, 9]);
}

function settle($db) {
    $stmt = $db->query("SELECT * FROM bets WHERE status = 0");
    $bets = $stmt->fetchAll();

    foreach ($bets as $bet) {
        $resStmt = $db->prepare("SELECT numbers, total_sum FROM lottery_results WHERE issue_no = ?");
        $resStmt->execute(array($bet['issue_no']));
        $result = $resStmt->fetch();

        if ($result) {
            $totalSum = (int)$result['total_sum'];
            $numbersStr = $result['numbers'];
            $nums = explode(',', $numbersStr);
            $isWin = false;
            $isReturn = false;

            // Logic for win check
            if ($bet['play_type'] == 'big' && $totalSum >= 14) $isWin = true;
            if ($bet['play_type'] == 'small' && $totalSum <= 13) $isWin = true;
            if ($bet['play_type'] == 'single' && $totalSum % 2 != 0) $isWin = true;
            if ($bet['play_type'] == 'double' && $totalSum % 2 == 0) $isWin = true;

            // Special Plays
            if ($bet['play_type'] == 'triple' && isTriple($numbersStr)) $isWin = true;
            if ($bet['play_type'] == 'straight' && isStraight($numbersStr)) $isWin = true;
            if ($bet['play_type'] == 'pair' && isPair($numbersStr)) $isWin = true;

            // Banker/Player/Tie
            if ($bet['play_type'] == 'banker' && $nums[0] > $nums[2]) $isWin = true;
            if ($bet['play_type'] == 'player' && $nums[2] > $nums[0]) $isWin = true;
            if ($bet['play_type'] == 'tie' && $nums[0] == $nums[2]) $isWin = true;

            if (is_numeric($bet['play_type']) && $totalSum == (int)$bet['play_type']) $isWin = true;

            if ($bet['odds_type'] == 'low') {
                if ($totalSum == 13 || $totalSum == 14) {
                    if (!is_numeric($bet['play_type'])) $isWin = false;
                }
            } else if ($bet['odds_type'] == 'high') {
                if (!$isWin) {
                    if ($totalSum == 13 || $totalSum == 14 || isTriple($numbersStr) || isPair($numbersStr) || isStraight($numbersStr)) {
                        $isReturn = true;
                    }
                }
            }

            $status = $isWin ? 1 : ($isReturn ? 3 : 2);
            $winAmount = $isWin ? $bet['bet_amount'] * $bet['odds'] : ($isReturn ? $bet['bet_amount'] : 0);

            $db->beginTransaction();
            try {
                $updateStmt = $db->prepare("UPDATE bets SET status = ?, win_amount = ? WHERE id = ?");
                $updateStmt->execute(array($status, $winAmount, $bet['id']));

                if ($winAmount > 0) {
                    \App\Model\User::updateBalance($bet['user_id'], $winAmount, 'win', "中奖回款: " . $bet['play_type'] . " (" . $bet['issue_no'] . ")", $db);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }

            checkAgentTurnoverReward($db, $bet['user_id']);
        }
    }
}

function checkAgentTurnoverReward($db, $userId) {
    $stmt = $db->prepare("SELECT inviter_id, total_turnover FROM users WHERE id = ?");
    $stmt->execute(array($userId));
    $user = $stmt->fetch();
    if ($user && $user['inviter_id'] && $user['total_turnover'] >= 1000) {
        $checkStmt = $db->prepare("SELECT id FROM rebates WHERE sub_id = ? AND user_id = ? AND type = 'turnover'");
        $checkStmt->execute(array($userId, $user['inviter_id']));
        if (!$checkStmt->fetch()) {
            $db->beginTransaction();
            try {
                \App\Model\User::updateBalance($user['inviter_id'], 10, 'rebate', '下级流水奖励', $db);
                $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'turnover', 10)")->execute(array($user['inviter_id'], $userId));
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }
}
settle($db);
