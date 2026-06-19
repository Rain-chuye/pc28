<?php
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $qq = $data['qq'] ?? '';
    $inviterId = $data['inviter_id'] ?? null;

    if (!$username || !$password || !$qq) {
        echo json_encode(['success' => false, 'message' => '请填写完整注册信息']);
        die;
    }

    $db = \App\Utils\DB::getInstance()->getConnection();

    // Check user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '用户名已存在']);
        die;
    }

    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, qq_number, inviter_id, balance) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$username, $password, $qq, $inviterId]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '注册失败: ' . $e->getMessage()]);
    }
}
