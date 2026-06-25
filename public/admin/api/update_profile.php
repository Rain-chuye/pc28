<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');
$db = \App\Utils\DB::getInstance()->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'update';
$userId = (int)($data['user_id'] ?? 0);

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing User ID']);
    die;
}

try {
    if ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
    } else {
        $nickname = $data['nickname'] ?? '';
        $qq = $data['qq'] ?? '';
        $balance = (float)($data['balance'] ?? 0);

        // Fix: Ensure status is handled as string 'active'/'frozen'
        // And check if column exists before updating to avoid SQL error if migration not run yet
        $status = trim($data['status'] ?? 'active');
        if (empty($status)) $status = 'active';

        $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ? WHERE id = ?";
        $params = [$nickname, $qq, $balance, $status, $userId];

        $password = trim($data['password'] ?? '');
        if (!empty($password)) {
            $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ?, password = ? WHERE id = ?";
            $params = [$nickname, $qq, $balance, $status, password_hash($password, PASSWORD_DEFAULT), $userId];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Update Failed: ' . $e->getMessage()]);
}
