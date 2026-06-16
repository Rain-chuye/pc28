<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

function settle($db) {
    // Get all pending bets
    $stmt = $db->query("SELECT * FROM bets WHERE status = 0");
    $bets = $stmt->fetchAll();

    foreach ($bets as $bet) {
        // Get result for the issue
        $resStmt = $db->prepare("SELECT total_sum FROM lottery_results WHERE issue_no = ?");
        $resStmt->execute(array($bet['issue_no']));
        $result = $resStmt->fetch();

        if ($result) {
            $totalSum = $result['total_sum'];
            $isWin = false;

            // Basic logic for Big/Small/Single/Double
            if ($bet['play_type'] == 'big' && $totalSum >= 14) $isWin = true;
            if ($bet['play_type'] == 'small' && $totalSum <= 13) $isWin = true;
            if ($bet['play_type'] == 'single' && $totalSum % 2 != 0) $isWin = true;
            if ($bet['play_type'] == 'double' && $totalSum % 2 == 0) $isWin = true;

            // Number bet
            if (is_numeric($bet['play_type']) && $totalSum == (int)$bet['play_type']) $isWin = true;

            $status = $isWin ? 1 : 2;
            $winAmount = $isWin ? $bet['bet_amount'] * $bet['odds'] : 0;

            $updateStmt = $db->prepare("UPDATE bets SET status = ?, win_amount = ? WHERE id = ?");
            $updateStmt->execute(array($status, $winAmount, $bet['id']));

            if ($isWin) {
                $userStmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $userStmt->execute(array($winAmount, $bet['user_id']));
            }
        }
    }
}

settle($db);
echo "Settlement completed.\n";
