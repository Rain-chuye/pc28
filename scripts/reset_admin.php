<?php
/**
 * 管理员重置工具
 */
require_once __DIR__ . '/../src/Utils/DB.php';

$username = 'admin';
$password = 'admin123'; // 默认密码

$db = \App\Utils\DB::getInstance()->getConnection();

try {
    // 检查是否存在
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin' WHERE id = ?");
        $stmt->execute([$password, $user['id']]);
        echo "管理员密码已重置为: $password\n";
    } else {
        $stmt = $db->prepare("INSERT INTO users (username, password, role, balance) VALUES (?, ?, 'admin', 0)");
        $stmt->execute([$username, $password]);
        echo "管理员账号已创建。用户名: $username, 密码: $password\n";
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
