<?php
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/Bet.php';
require_once __DIR__ . '/../src/Model/User.php';

use App\Utils\DB;
use App\Model\Bet;

$db = DB::getInstance()->getConnection();

// Get robots
$stmt = $db->query("SELECT id FROM users WHERE is_robot = 1");
$robots = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($robots)) {
    // Create some robots if none exist
    for ($i = 0; $i < 20; $i++) {
        $username = "bot_" . bin2hex(random_bytes(3));
        $db->prepare("INSERT INTO users (username, password, balance, is_robot) VALUES (?, 'robot', 1000000, 1)")
           ->execute([$username]);
    }
    $robots = $db->query("SELECT id FROM users WHERE is_robot = 1")->fetchAll(PDO::FETCH_COLUMN);
}

// Latest issue
$stmt = $db->query("SELECT issue_no FROM lottery_results ORDER BY id DESC LIMIT 1");
$latestIssue = $stmt->fetchColumn();
$currentIssue = $latestIssue + 1;

$playTypes = ['big', 'small', 'single', 'double', 'big_single', 'big_double', 'small_single', 'small_double', '0', '13', '14', '27', 'triple', 'straight'];

// Place 3-5 bets per execution for "high density"
$betCount = rand(3, 8);
for ($i = 0; $i < $betCount; $i++) {
    $robotId = $robots[array_rand($robots)];
    $playType = $playTypes[array_rand($playTypes)];
    $amount = rand(10, 500);
    $oddsType = (rand(0, 10) > 7) ? 'high' : 'low';

    try {
        Bet::place($robotId, $currentIssue, $playType, $amount, $oddsType);
        echo "Robot $robotId placed $amount on $playType ($oddsType)\n";
    } catch (Exception $e) {
        // Balance might be low even for bots if not careful
    }
}
