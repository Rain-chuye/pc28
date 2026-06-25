<?php
/**
 * Admin Recovery Script
 * Usage: php scripts/reset_admin.php <username> <new_password>
 */
require_once __DIR__ . '/../src/Utils/DB.php';

if ($argc < 3) {
    die("Usage: php reset_admin.php <username> <new_password>\n");
}

$user = $argv[1];
$pass = $argv[2];

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE users SET password = ?, status = 'active', role = 'admin' WHERE username = ?");
    $stmt->execute([$hash, $user]);

    if ($stmt->rowCount() > 0) {
        echo "Successfully updated password and activated user '{$user}' as admin.\n";
    } else {
        echo "Error: User '{$user}' not found in database.\n";
    }
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
