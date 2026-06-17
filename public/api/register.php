<?php
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];
    $inviterId = isset($data['inviter_id']) ? (int)$data['inviter_id'] : null;

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '用户名或密码不能为空']);
        die;
    }

    $db = \App\Utils\DB::getInstance()->getConnection();

    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '用户名已存在']);
        die;
    }

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, password, inviter_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $password, $inviterId])) {
        echo json_encode(['success' => true, 'message' => '注册成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '注册失败']);
    }
}
