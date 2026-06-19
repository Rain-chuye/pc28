<?php
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $qq = isset($data['qq']) ? trim($data['qq']) : '';
        $inviterId = !empty($data['inviter_id']) ? (int)$data['inviter_id'] : null;

        if (!$username || !$password || !$qq) {
            echo json_encode(['success' => false, 'message' => '请填写完整注册信息 (账号/密码/QQ)']);
        } else {
            $db = \App\Utils\DB::getInstance()->getConnection();

            // Check user exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => '用户名已存在']);
            } else {
                $stmt = $db->prepare("INSERT INTO users (username, password, qq_number, inviter_id, balance, status, role) VALUES (?, ?, ?, ?, 0, 1, 'user')");
                $stmt->execute([$username, $password, $qq, $inviterId]);
                echo json_encode(['success' => true]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => '仅限 POST 请求']);
    }
} catch (Exception $e) {
    error_log("Register Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '注册异常，请稍后再试',
        'debug' => $e->getMessage()
    ]);
}
