<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

function testReturn($db) {
    echo "--- Testing High Odds Return for Numbers ---\n";

    // Clear
    $db->exec("DELETE FROM bets");
    $db->exec("DELETE FROM lottery_results");

    // 1. Result is 14
    $issue = 'H001';
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES (?, '4,5,5', 14, NOW())")->execute([$issue]);

    // User bets '5' (Specific Number) on HIGH odds
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (2, ?, '5', 'high', 100, 12.0, 0)")->execute([$issue]);

    include __DIR__ . '/../scripts/settle.php';

    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'H001'")->fetch();
    echo "Bet on '5' when Result is 14: Status = " . $bet['status'] . " (Expected 3), WinAmount = " . $bet['win_amount'] . " (Expected 100)\n";

    // 2. Result is Triple 3 (Sum 9)
    $issue = 'H002';
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES (?, '3,3,3', 9, NOW())")->execute([$issue]);
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (2, ?, '5', 'high', 100, 12.0, 0)")->execute([$issue]);

    settle($db);

    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'H002'")->fetch();
    echo "Bet on '5' when Result is Triple: Status = " . $bet['status'] . " (Expected 3), WinAmount = " . $bet['win_amount'] . " (Expected 100)\n";
}

try {
    testReturn($db);
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
