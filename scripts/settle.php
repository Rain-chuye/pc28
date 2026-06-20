<?php
/**
 * PC28 结算系统 - 精确规则修正版 (V11)
 */
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
    $nums = array_map('intval', explode(',', $numbers));
    sort($nums);
    if ($nums[1] == $nums[0] + 1 && $nums[2] == $nums[1] + 1) return true;
    $set = array_values($nums);
    if ($set === [0, 1, 9] || $set === [0, 8, 9]) return true;
    return false;
}

function settle($db) {
    $stmt = $db->query("SELECT * FROM bets WHERE status = 0 LIMIT 500");
    $bets = $stmt->fetchAll();

    foreach ($bets as $bet) {
        $resStmt = $db->prepare("SELECT numbers, total_sum FROM lottery_results WHERE issue_no = ?");
        $resStmt->execute(array($bet['issue_no']));
        $result = $resStmt->fetch();

        if ($result) {
            $totalSum = (int)$result['total_sum'];
            $numbersStr = $result['numbers'];
            $isWin = false;
            $isReturn = false;
            $finalOdds = (float)$bet['odds'];

            $playType = $bet['play_type'];
            $room = $bet['odds_type']; // 'high' = 2.8, 'low' = 2.0

            // 1. Basic Win Condition
            switch($playType) {
                case 'big': if ($totalSum >= 14) $isWin = true; break;
                case 'small': if ($totalSum <= 13) $isWin = true; break;
                case 'single': if ($totalSum % 2 != 0) $isWin = true; break;
                case 'double': if ($totalSum % 2 == 0) $isWin = true; break;
                case 'big_single': if ($totalSum >= 14 && $totalSum % 2 != 0) $isWin = true; break;
                case 'big_double': if ($totalSum >= 14 && $totalSum % 2 == 0) $isWin = true; break;
                case 'small_single': if ($totalSum <= 13 && $totalSum % 2 != 0) $isWin = true; break;
                case 'small_double': if ($totalSum <= 13 && $totalSum % 2 == 0) $isWin = true; break;
                case 'extreme_big': if ($totalSum >= 22) $isWin = true; break;
                case 'extreme_small': if ($totalSum <= 5) $isWin = true; break;
                case 'triple': if (isTriple($numbersStr)) $isWin = true; break;
                case 'straight': if (isStraight($numbersStr)) $isWin = true; break;
                case 'pair': if (isPair($numbersStr)) $isWin = true; break;
                default:
                    if (is_numeric($playType) && $totalSum == (int)$playType) $isWin = true;
            }

            // 2. Room Rule Processing
            if ($room == 'high') {
                // 高倍房：开13/14/对子/顺子/豹子 -> 中奖回本 (Odds=1.0), 没中也回本
                $isSpecial = ($totalSum == 13 || $totalSum == 14 || isPair($numbersStr) || isStraight($numbersStr) || isTriple($numbersStr));
                if ($isSpecial) {
                    $isWin = true;
                    $finalOdds = 1.0;
                }
            } else {
                // 低倍房：开13、14
                if ($totalSum == 13 || $totalSum == 14) {
                    $isCombo = in_array($playType, ['big_single','big_double','small_single','small_double']);
                    $isBSSD = in_array($playType, ['big','small','single','double']);

                    if ($isCombo) {
                        // 组合全吃 (Lose)
                        $isWin = false;
                        $isReturn = false;
                    } elseif ($isBSSD && $isWin) {
                        // 大小单双中奖只赚1.6倍
                        $finalOdds = 1.60;
                    }
                }
            }

            $status = $isWin ? 1 : ($isReturn ? 3 : 2);
            $winAmount = $isWin ? $bet['bet_amount'] * $finalOdds : ($isReturn ? $bet['bet_amount'] : 0);

            $db->beginTransaction();
            try {
                $updateStmt = $db->prepare("UPDATE bets SET status = ?, win_amount = ?, odds = ? WHERE id = ?");
                $updateStmt->execute(array($status, $winAmount, $finalOdds, $bet['id']));

                if ($winAmount > 0) {
                    \App\Model\User::updateBalance($bet['user_id'], $winAmount, 'win', "结算派奖: " . $playType . " (" . $bet['issue_no'] . ")", $db);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }
}
settle($db);
