<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '请输入账号和密码']);
        die;
    }

    try {
        $db = \App\Utils\DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => '用户不存在']);
            die;
        }

        $valid = false;
        if (password_verify($password, $user['password'])) {
            $valid = true;
        } elseif ($password === $user['password']) {
            $valid = true;
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        }

        if ($valid) {
            // Flexible Status Check (Handles INT or STRING)
            $status = $user['status'] ?? 'active';
            if ($status === 'frozen' || $status === '0' || $status === 0) {
                echo json_encode(['success' => false, 'message' => '您的账号已被冻结，请联系客服']);
                die;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '密码错误']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '登录异常: ' . $e->getMessage()]);
    }
}
