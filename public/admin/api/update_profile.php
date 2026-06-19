<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = (int)$data['user_id'];
    $nickname = trim($data['nickname'] ?? '');
    $qq = trim($data['qq'] ?? '');
    $balance = (float)($data['balance'] ?? 0);
    $password = trim($data['password'] ?? '');

    $db = \App\Utils\DB::getInstance()->getConnection();

    $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?";
    $params = [$nickname, $qq, $balance];

    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = $password;
    }

    $sql .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
