<?php
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/Bet.php';

$db = \App\Utils\DB::getInstance()->getConnection();

// Get active issue
$issueStmt = $db->query("SELECT issue_no FROM lottery_results ORDER BY id DESC LIMIT 1");
$lastIssue = $issueStmt->fetchColumn();
// Increment issue no for next bet (simulated)
$issueNo = (int)$lastIssue + 1;

// Get robots
$robots = $db->query("SELECT id FROM users WHERE is_robot = 1")->fetchAll(\PDO::FETCH_COLUMN);

if (empty($robots)) exit("No robots found.\n");

$playTypes = ['big', 'small', 'single', 'double', 'big_single', 'big_double', 'small_single', 'small_double'];

foreach ($robots as $botId) {
    // Random chance to bet
    if (rand(1, 10) > 4) {
        $playType = $playTypes[array_rand($playTypes)];
        $amount = rand(10, 500);
        $oddsType = rand(0, 1) ? 'low' : 'high';

        try {
            \App\Model\Bet::place($botId, $issueNo, $playType, $amount, $oddsType);
            echo "Bot $botId placed bet on $playType amount $amount\n";
        } catch (\Exception $e) {
            echo "Bot $botId failed: " . $e->getMessage() . "\n";
        }
    }
}
