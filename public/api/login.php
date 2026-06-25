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

        // 1. Check if user exists
        if (!$user) {
            echo json_encode(['success' => false, 'message' => '用户不存在']);
            die;
        }

        // 2. Flexible Password Check (Hashed vs Plaintext)
        $valid = false;
        if (password_verify($password, $user['password'])) {
            $valid = true;
        } elseif ($password === $user['password']) {
            // Plaintext fallback (usually for newly seeded bots/admins)
            $valid = true;
            // Auto-upgrade to hash for security
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upStmt->execute([$hash, $user['id']]);
        }

        if ($valid) {
            // 3. Status Check (if column exists)
            $status = $user['status'] ?? 'active';
            if ($status === 'frozen') {
                echo json_encode(['success' => false, 'message' => '您的账号已被冻结，请联系客服']);
                die;
            }

            // 4. Session Setup
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '密码错误']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '数据库连接失败: ' . $e->getMessage()]);
    }
}
