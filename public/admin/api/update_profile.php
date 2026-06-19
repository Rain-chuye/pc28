<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$data = json_decode(file_get_contents('php://input'), true);

$newUsername = $data['username'] ?? '';
$newPassword = $data['password'] ?? '';

if (!$newUsername) {
    echo json_encode(['success' => false, 'message' => '用户名不能为空']);
    die;
}

try {
    if ($newPassword) {
        $stmt = $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newPassword, $_SESSION['user_id']]);
    } else {
        $stmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$newUsername, $_SESSION['user_id']]);
    }

    $_SESSION['username'] = $newUsername;
    echo json_encode(['success' => true, 'message' => '个人信息已更新']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
}
