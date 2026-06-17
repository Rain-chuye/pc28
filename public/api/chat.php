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
    $message = $data['message'];
    $receiverId = isset($data['receiver_id']) ? (int)$data['receiver_id'] : 1; // Default to admin (id 1)

    if ($role === 'admin') {
        // Admin replying to user
    } else {
        $receiverId = 1; // Users always chat with admin
    }

    $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $receiverId, $message]);

    echo json_encode(['success' => true]);
} else {
    // GET: load messages
    if ($role === 'admin') {
        $targetUserId = (int)$_GET['user_id'];
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id ASC");
        $stmt->execute([$userId, $targetUserId, $targetUserId, $userId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE (sender_id = ? AND receiver_id = 1) OR (sender_id = 1 AND receiver_id = ?) ORDER BY id ASC");
        $stmt->execute([$userId, $userId]);
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
