<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $msg = $data['message'] ?? '';
    $receiverId = $data['receiver_id'] ?? 1; // Default to admin (ID 1)

    if (!$msg) {
        echo json_encode(['success' => false, 'message' => '内容不能为空']);
        die;
    }

    $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $receiverId, $msg]);
    echo json_encode(['success' => true]);
} else {
    // Fetch conversation
    if ($role === 'admin') {
        // Admin sees all distinct users who messaged them
        $targetUser = $_GET['user_id'] ?? 0;
        if ($targetUser) {
            $stmt = $db->prepare("SELECT * FROM chat_messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id ASC");
            $stmt->execute([$userId, $targetUser, $targetUser, $userId]);
        } else {
             $stmt = $db->prepare("SELECT DISTINCT sender_id FROM chat_messages WHERE receiver_id = ?");
             $stmt->execute([$userId]);
        }
    } else {
        // User sees conversation with Admin (ID 1)
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE (sender_id = ? AND receiver_id = 1) OR (sender_id = 1 AND receiver_id = ?) ORDER BY id ASC");
        $stmt->execute([$userId, $userId]);
    }
    $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $msgs]);
}
