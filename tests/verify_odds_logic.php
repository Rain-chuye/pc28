<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

function testOddsSettlement($db) {
    echo "--- Testing New Odds & Banker/Player/Tie Logic ---\n";

    $db->exec("DELETE FROM bets");
    $db->exec("DELETE FROM lottery_results");

    // Case 1: Banker Win (1st > 3rd)
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES ('B001', '8,2,3', 13, NOW())")->execute();
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds, status) VALUES (2, 'B001', 'banker', 100, 3.0, 0)")->execute();

    include __DIR__ . '/../scripts/settle.php';
    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'B001'")->fetch();
    echo "Banker Win (8 vs 3): Status = " . $bet['status'] . " (Expected 1), WinAmount = " . $bet['win_amount'] . " (Expected 300)\n";

    // Case 2: Number 0 High Odds (888x)
    $db->prepare("INSERT INTO lottery_results (issue_no, numbers, total_sum, open_time) VALUES ('N000', '0,0,0', 0, NOW())")->execute();
    $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds, status) VALUES (2, 'N000', '0', 10, 888.0, 0)")->execute();

    settle($db);
    $bet = $db->query("SELECT * FROM bets WHERE issue_no = 'N000'")->fetch();
    echo "Number 0 (888x): Status = " . $bet['status'] . " (Expected 1), WinAmount = " . $bet['win_amount'] . " (Expected 8880)\n";
}

testOddsSettlement($db);
