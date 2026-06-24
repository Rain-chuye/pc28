<?php
require_once 'src/Utils/DB.php';
try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE is_robot = 1");
    echo "Current Bot Count: " . $stmt->fetchColumn() . "\n";

    // Check robot nicknames
    $stmt = $db->query("SELECT nickname FROM users WHERE is_robot = 1 LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo $e->getMessage();
}
