<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

// Mock settle function or include script
function settle_test($db) {
    include __DIR__ . '/../scripts/settle.php';
}

function testSettlement($db) {
    echo "--- Testing Settlement Logic ---\n";

    // Clear old data for test
    $db->exec("DELETE FROM bets");
    $db->exec("DELETE FROM lottery_results");
    $db->prepare("UPDATE users SET balance = 1000, daily_turnover = 0, total_turnover = 0 WHERE id = ?")->execute([2]);

    // Case 1: Low Odds - 13 Result (Should lose principal)
    $issue = 'TEST001';
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES (?, '4,4,5', 13, NOW())")->execute([$issue]);

    // User bets 'small' on low odds
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (2, ?, 'small', 'low', 100, 2.0, 0)")->execute([$issue]);

    include __DIR__ . '/../scripts/settle.php';

    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'TEST001'")->fetch();
    echo "Low Odds Small on 13: Status = " . $bet['status'] . " (Expected 2), WinAmount = " . $bet['win_amount'] . " (Expected 0)\n";

    // Case 2: High Odds - 13 Result (Should return principal)
    $issue = 'TEST002';
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES (?, '4,4,5', 13, NOW())")->execute([$issue]);
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (2, ?, 'small', 'high', 100, 1.95, 0)")->execute([$issue]);

    settle($db);

    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'TEST002'")->fetch();
    echo "High Odds Small on 13: Status = " . $bet['status'] . " (Expected 3), WinAmount = " . $bet['win_amount'] . " (Expected 100)\n";

    // Case 3: High Odds - Triple (Should return principal)
    $issue = 'TEST003';
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES (?, '3,3,3', 9, NOW())")->execute([$issue]);
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (2, ?, 'small', 'high', 100, 1.95, 0)")->execute([$issue]);

    settle($db);

    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'TEST003'")->fetch();
    echo "High Odds Small on Triple(9): Status = " . $bet['status'] . " (Expected 3), WinAmount = " . $bet['win_amount'] . " (Expected 100)\n";
}

try {
    testSettlement($db);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
