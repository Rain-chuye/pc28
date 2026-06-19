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

    // 2. Safe Migrations Utility
    function addColumnSafe($db, $table, $column, $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            echo "Added column $column to $table.\n";
        }
    }

    addColumnSafe($db, 'users', 'nickname', "VARCHAR(50) DEFAULT NULL AFTER username");
    addColumnSafe($db, 'finance_requests', 'refusal_reason', "VARCHAR(255) DEFAULT NULL AFTER status");

    // 3. New Tables
    $db->exec("CREATE TABLE IF NOT EXISTS bot_rules (
        id INT(11) NOT NULL AUTO_INCREMENT,
        keyword VARCHAR(100) DEFAULT NULL,
        response TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Update Settings and Data
    $odds_sql = file_get_contents(__DIR__ . '/../database/update_odds_rules.sql');
    $db->exec($odds_sql);

    $settings = [
        ['chat_mute_all', '0'],
        ['custom_draw_interval', '300'],
        ['bot_auto_reply_enabled', '1']
    ];
    foreach($settings as $s) {
        $st = $db->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        $st->execute($s);
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
