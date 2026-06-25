<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$action = $_GET['action'] ?? 'get';
$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

try {
    if ($action === 'get') {
        if(!$userId) throw new Exception("Unauthorized");
        $stmt = $db->prepare("SELECT * FROM chat_messages WHERE sender_id = ? OR receiver_id = ? ORDER BY id ASC");
        $stmt->execute([$userId, $userId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'send') {
        if(!$userId) throw new Exception("Unauthorized");
        $data = json_decode(file_get_contents('php://input'), true);
        $msg = trim($data['message'] ?? '');
        $type = $data['type'] ?? 'text';

        if(!$msg) throw new Exception("Message empty");
        if ($type === 'image' && strlen($msg) > 1000000) throw new Exception("Image too large");

        $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, type) VALUES (?, 0, ?, ?)");
        $stmt->execute([$userId, $msg, $type]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'get_all_admin' && $isAdmin) {
        $stmt = $db->query("SELECT c.*, u.username as sender_name FROM chat_messages c LEFT JOIN users u ON c.sender_id = u.id ORDER BY c.id ASC LIMIT 500");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'send_admin' && $isAdmin) {
        $data = json_decode(file_get_contents('php://input'), true);
        $targetId = (int)$data['user_id'];
        $msg = trim($data['message'] ?? '');
        $type = $data['type'] ?? 'text';

        $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, type) VALUES (0, ?, ?, ?)");
        $stmt->execute([$targetId, $msg, $type]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'clear_private' && $isAdmin) {
        $uid = (int)$_GET['user_id'];
        if(!$uid) {
            $db->query("TRUNCATE TABLE chat_messages"); // Clear all
        } else {
            $stmt = $db->prepare("DELETE FROM chat_messages WHERE sender_id = ? OR receiver_id = ?");
            $stmt->execute([$uid, $uid]);
        }
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
