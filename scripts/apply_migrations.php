<?php
// Manual autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Utils\DB;

try {
    $db = DB::getInstance()->getConnection();
    echo "Connected to database.\n";

    // 1. Initial Schema
    $schema = file_get_contents(__DIR__ . '/../database/mysql_schema.sql');
    $db->exec($schema);
    echo "Base schema applied/verified.\n";

    // 2. Safe Migrations
    function addColumnSafe($db, $table, $column, $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            echo "Added column $column to $table.\n";
        } else {
            echo "Column $column already exists in $table.\n";
        }
    }

    addColumnSafe($db, 'users', 'qq_number', "VARCHAR(20) DEFAULT NULL AFTER role");
    addColumnSafe($db, 'lottery_results', 'next_draw_at', "DATETIME DEFAULT NULL AFTER open_time");
    addColumnSafe($db, 'chat_messages', 'reply_to', "INT(11) DEFAULT NULL");

    // 3. Apply Odds and Settings
    $odds_sql = file_get_contents(__DIR__ . '/../database/update_odds_rules.sql');
    $db->exec($odds_sql);
    echo "Odds and settings updated.\n";

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
