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
        $type = $data['type'] ?? 'text'; // 'text' or 'image'

        if(!$msg) throw new Exception("内容不能为空");

        // Security check for base64 images
        if ($type === 'image' && strlen($msg) > 500000) {
            throw new Exception("图片文件过大，请压缩后上传");
        }

        $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, type) VALUES (?, 0, ?, ?)");
        $stmt->execute([$userId, $msg, $type]);

        // Auto-reply logic (Existing)
        $botEnabled = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'bot_auto_reply_enabled'")->fetchColumn();
        if($botEnabled == '1' && $type === 'text') {
            $rules = $db->query("SELECT * FROM bot_rules WHERE is_active = 1")->fetchAll();
            foreach($rules as $rule) {
                if(!empty($rule['keyword']) && mb_strpos($msg, $rule['keyword']) !== false) {
                    $st = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (0, ?, ?)");
                    $st->execute([$userId, $rule['response']]);
                    break;
                }
            }
        }
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'get_all_admin' && $isAdmin) {
        $stmt = $db->query("SELECT c.*, u.username as sender_name FROM chat_messages c LEFT JOIN users u ON c.sender_id = u.id ORDER BY c.id ASC LIMIT 200");
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
