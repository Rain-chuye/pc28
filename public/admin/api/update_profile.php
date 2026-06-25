<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');
$db = \App\Utils\DB::getInstance()->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'update';
$userId = $data['user_id'];

try {
    if ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
    } else {
        $nickname = $data['nickname'] ?? '';
        $qq = $data['qq'] ?? '';
        $balance = (float)($data['balance'] ?? 0);
        $status = $data['status'] ?? 'active';
        $password = $data['password'] ?? '';

        $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ? WHERE id = ?";
        $params = [$nickname, $qq, $balance, $status, $userId];

        if (!empty($password)) {
            $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ?, password = ? WHERE id = ?";
            $params = [$nickname, $qq, $balance, $status, password_hash($password, PASSWORD_DEFAULT), $userId];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
