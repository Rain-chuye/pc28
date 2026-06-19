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
        if(!$msg) throw new Exception("Message empty");

        $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, 0, ?)");
        $stmt->execute([$userId, $msg]);

        // Bot Auto-reply logic
        $settings = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'bot_auto_reply_enabled'")->fetch(PDO::FETCH_KEY_PAIR);
        if(($settings['bot_auto_reply_enabled'] ?? '1') == '1') {
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
        $stmt = $db->query("SELECT c.*, u.username as sender_name FROM chat_messages c LEFT JOIN users u ON c.sender_id = u.id ORDER BY c.id ASC LIMIT 100");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'send_admin' && $isAdmin) {
        $data = json_decode(file_get_contents('php://input'), true);
        $targetId = (int)$data['user_id'];
        $msg = trim($data['message'] ?? '');
        $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (0, ?, ?)");
        $stmt->execute([$targetId, $msg]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'get_bot_rules') {
        $stmt = $db->query("SELECT * FROM bot_rules");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'add_bot_rule' && $isAdmin) {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("INSERT INTO bot_rules (keyword, response) VALUES (?, ?)");
        $stmt->execute([$data['keyword'], $data['response']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_bot_rule' && $isAdmin) {
        $stmt = $db->prepare("DELETE FROM bot_rules WHERE id = ?");
        $stmt->execute([(int)$_GET['id']]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
