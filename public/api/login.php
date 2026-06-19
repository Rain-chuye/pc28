<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => '请输入用户名和密码']);
        } else {
            $username = trim($data['username']);
            $password = $data['password'];

            $db = \App\Utils\DB::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && $user['password'] === $password) {
                if ($user['status'] != 1) {
                    echo json_encode(['success' => false, 'message' => '账号已被禁用']);
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    echo json_encode(['success' => true, 'role' => $user['role']]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => '仅限 POST 请求']);
    }
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '服务器异常，请稍后再试',
        'debug' => $e->getMessage()
    ]);
}
