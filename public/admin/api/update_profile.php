<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'];
    $nickname = trim($data['nickname'] ?? '');
    $qq = trim($data['qq'] ?? '');
    $balance = isset($data['balance']) ? (float)$data['balance'] : null;
    $password = trim($data['password'] ?? '');

    $db = \App\Utils\DB::getInstance()->getConnection();

    // Handle admin self-update
    if($userId === 'admin') {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE role = 'admin'");
        $stmt->execute([$password]);
        echo json_encode(['success' => true]);
    } else {
        $userId = (int)$userId;
        $sql = "UPDATE users SET nickname = ?, qq_number = ?";
        $params = [$nickname, $qq];

        if ($balance !== null) {
            $sql .= ", balance = ?";
            $params[] = $balance;
        }

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = $password;
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
