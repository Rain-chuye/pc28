<?php
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $qq = $data['qq'] ?? '';
    $newPassword = $data['password'] ?? '';

    if (!$username || !$qq || !$newPassword) {
        echo json_encode(['success' => false, 'message' => '信息不完整']);
        die;
    }

    $db = \App\Utils\DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND qq_number = ?");
    $stmt->execute([$username, $qq]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '账号或QQ校验失败']);
        die;
    }

    try {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPassword, $user['id']]);
        echo json_encode(['success' => true, 'message' => '密码已重置，请登录']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '重置失败']);
    }
}
