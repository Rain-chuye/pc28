<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

function checkFile($path) {
    return file_exists($path) ? '<span style="color:green">√ 存在</span>' : '<span style="color:red">× 缺失</span>';
}

$db_config = require __DIR__ . '/../src/Config/database.php';
$conn_status = '未测试';
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};port={$db_config['port']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password']);
    $conn_status = '<span style="color:green">√ 连接成功</span>';
} catch (Exception $e) {
    $conn_status = '<span style="color:red">× 连接失败: ' . $e->getMessage() . '</span>';
}

echo "<h1>PC28 系统运行环境自检</h1>";
echo "<ul>";
echo "<li><b>PHP 版本:</b> " . PHP_VERSION . "</li>";
echo "<li><b>当前运行目录:</b> " . __DIR__ . " (应为 .../public)</li>";
echo "<li><b>防跨站检测 (open_basedir):</b> " . (ini_get('open_basedir') ?: '已关闭 (推荐)') . "</li>";
echo "<li><b>数据库连接:</b> $conn_status</li>";
echo "<li><b>核心逻辑目录 (/src):</b> " . checkFile(__DIR__ . '/../src/Utils/DB.php') . "</li>";
echo "<li><b>管理员验证模块:</b> " . checkFile(__DIR__ . '/admin/check_auth.php') . "</li>";
echo "<li><b>当前会话 Role:</b> " . ($_SESSION['role'] ?? '未登录') . "</li>";
echo "</ul>";

echo "<h3>调试建议:</h3>";
echo "1. 如果<b>核心逻辑目录</b>显示缺失，说明宝塔的 '防跨站攻击 (open_basedir)' 限制了访问，请在网站设置中关闭它。<br>";
echo "2. 如果<b>数据库连接</b>失败，请检查 <code>src/Config/database.php</code> 中的账号密码。<br>";
echo "3. 访问后台地址: <a href='/admin/index.php'>/admin/index.php</a>";
