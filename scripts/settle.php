<?php
/**
 * PC28 结算系统完善版
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
    $nums = explode(',', $numbers);
    sort($nums);
    // 正常顺子 or 089 特殊顺子
    return ($nums[1] == $nums[0] + 1 && $nums[2] == $nums[1] + 1) || (array_slice($nums, 0, 3) == [0, 8, 9]);
}

function settle($db) {
    // 仅查询待结算注单
    $stmt = $db->query("SELECT * FROM bets WHERE status = 0 LIMIT 100");
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

            // 玩法逻辑校验
            switch($bet['play_type']) {
                case 'big': if ($totalSum >= 14) $isWin = true; break;
                case 'small': if ($totalSum <= 13) $isWin = true; break;
                case 'single': if ($totalSum % 2 != 0) $isWin = true; break;
                case 'double': if ($totalSum % 2 == 0) $isWin = true; break;
                case 'big_single': if ($totalSum >= 14 && $totalSum % 2 != 0) $isWin = true; break;
                case 'big_double': if ($totalSum >= 14 && $totalSum % 2 == 0) $isWin = true; break;
                case 'small_single': if ($totalSum <= 13 && $totalSum % 2 != 0) $isWin = true; break;
                case 'small_double': if ($totalSum <= 13 && $totalSum % 2 == 0) $isWin = true; break;
                case 'triple': if (isTriple($numbersStr)) $isWin = true; break;
                case 'straight': if (isStraight($numbersStr)) $isWin = true; break;
                case 'pair': if (isPair($numbersStr)) $isWin = true; break;
                case 'banker': if ($nums[0] > $nums[2]) $isWin = true; break;
                case 'player': if ($nums[2] > $nums[0]) $isWin = true; break;
                case 'tie': if ($nums[0] == $nums[2]) $isWin = true; break;
                default:
                    if (is_numeric($bet['play_type']) && $totalSum == (int)$bet['play_type']) $isWin = true;
            }

            // 模式补丁: 13, 14 规则
            if ($bet['odds_type'] == 'low') {
                if (($totalSum == 13 || $totalSum == 14) && !is_numeric($bet['play_type'])) {
                    $isWin = false; // 标准房 13/14 大小单双不中
                }
            } else if ($bet['odds_type'] == 'high') {
                if (!$isWin) {
                    // 高赔房/保本房: 遇到 13, 14 或 特殊牌型，若未中则退回本金
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
                error_log("Settle Error: " . $e->getMessage());
            }
        }
    }
}
settle($db);
