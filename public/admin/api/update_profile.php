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
        $statusStr = trim($data['status'] ?? 'active');

        // Robust Type Check for Status Column
        $stmtCol = $db->prepare("DESCRIBE users 'status'");
        $stmtCol->execute();
        $colInfo = $stmtCol->fetch();
        $isInteger = strpos(strtolower($colInfo['Type'] ?? ''), 'int') !== false;

        $finalStatus = $statusStr;
        if ($isInteger) {
            // Map strings to integers if the DB column is INT
            $finalStatus = ($statusStr === 'active' || $statusStr === '1') ? 1 : 0;
        }

        $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ? WHERE id = ?";
        $params = [$nickname, $qq, $balance, $finalStatus, $userId];

        $password = trim($data['password'] ?? '');
        if (!empty($password)) {
            $sql = "UPDATE users SET nickname = ?, qq_number = ?, balance = ?, status = ?, password = ? WHERE id = ?";
            $params = [$nickname, $qq, $balance, $finalStatus, password_hash($password, PASSWORD_DEFAULT), $userId];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Update Failed: ' . $e->getMessage()]);
}
