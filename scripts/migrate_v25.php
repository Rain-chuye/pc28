<?php
require_once __DIR__ . '/../src/Utils/DB.php';
try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    echo "Running migration V25...\n";
    $db->exec("ALTER TABLE users MODIFY COLUMN status VARCHAR(20) DEFAULT 'active'");
    $db->exec("UPDATE users SET status = 'active' WHERE status = '1' OR status = 1");
    $db->exec("UPDATE users SET status = 'frozen' WHERE status = '0' OR status = 0");
    echo "Migration Complete.\n";
} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
