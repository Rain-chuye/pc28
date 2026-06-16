<?php
require_once __DIR__ . '/../src/Utils/DB.php';
use App\Utils\DB;

function settleIssue($issueNo, $totalSum) {
    $db = DB::getInstance();
    $db->beginTransaction();
    try {
        // Find pending bets for this issue
        $stmt = $db->prepare("SELECT * FROM bets WHERE issue_no = ? AND status = 0 FOR UPDATE");
        $stmt->execute([$issueNo]);
        $bets = $stmt->fetchAll();

        foreach ($bets as $bet) {
            $isWin = checkWin($bet['play_type'], $totalSum);
            if ($isWin) {
                $winAmount = $bet['bet_amount'] * $bet['odds'];
                // Update bet
                $stmt = $db->prepare("UPDATE bets SET status = 1, win_amount = ? WHERE id = ?");
                $stmt->execute([$winAmount, $bet['id']]);
                // Add to user balance
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$winAmount, $bet['user_id']]);
            } else {
                // Update bet as lost
                $stmt = $db->prepare("UPDATE bets SET status = 2 WHERE id = ?");
                $stmt->execute([$bet['id']]);
            }
        }

        $db->commit();
        echo "Issue $issueNo settled.\n";
    } catch (\Exception $e) {
        $db->rollBack();
        echo "Error settling issue $issueNo: " . $e->getMessage() . "\n";
    }
}

function checkWin($playType, $sum) {
    switch ($playType) {
        case 'big': return $sum >= 14;
        case 'small': return $sum <= 13;
        case 'single': return $sum % 2 !== 0;
        case 'double': return $sum % 2 === 0;
        case 'big_single': return $sum >= 14 && $sum % 2 !== 0;
        case 'big_double': return $sum >= 14 && $sum % 2 === 0;
        case 'small_single': return $sum <= 13 && $sum % 2 !== 0;
        case 'small_double': return $sum <= 13 && $sum % 2 === 0;
        case 'extreme_big': return $sum >= 22;
        case 'extreme_small': return $sum <= 5;
        default: return false;
    }
}

// Logic to check latest unsettled results and call settleIssue
