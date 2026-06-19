<?php
require_once __DIR__ . '/src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

$sql = file_get_contents(__DIR__ . '/database/update_odds_rules.sql');
// Split by semicolon, filter out comments
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    try {
        $db->exec($stmt);
        echo "Executed: " . substr($stmt, 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "Error on statement: " . $e->getMessage() . "\n";
    }
}
