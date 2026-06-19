<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$data = json_decode(file_get_contents('php://input'), true);

try {
    $userId = $data['user_id'];
    $updates = [];
    $params = [];

    if (isset($data['balance'])) {
        $updates[] = "balance = ?";
        $params[] = (float)$data['balance'];
    }
    if (isset($data['qq'])) {
        $updates[] = "qq_number = ?";
        $params[] = $data['qq'];
    }
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = $data['password'];
    }

    if (empty($updates)) {
        throw new Exception("No fields to update");
    }

    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
