<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();
$action = $_GET['action'] ?? 'get';
$room = $_GET['room'] ?? 'high';

try {
    if ($action === 'get') {
        $stmt = $db->prepare("SELECT gm.*, COALESCE(u.nickname, u.username, '系统机器人') as username
                             FROM group_messages gm
                             LEFT JOIN users u ON gm.user_id = u.id
                             WHERE gm.room_type = ?
                             ORDER BY gm.id DESC LIMIT 100");
        $stmt->execute([$room]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'clear') {
        $stmt = $db->prepare("DELETE FROM group_messages WHERE room_type = ?");
        $stmt->execute([$room]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'send_broadcast') {
        $data = json_decode(file_get_contents('php://input'), true);
        $msg = trim($data['message'] ?? '');
        $type = $data['type'] ?? 'text'; // 'text' or 'red_packet'

        if ($type === 'red_packet') {
            $amount = (float)$data['amount'];
            $count = (int)$data['count'];
            $req = (float)($data['min_turnover'] ?? 0);

            $stmt = $db->prepare("INSERT INTO red_packets (total_amount, remaining_amount, total_count, remaining_count, min_turnover_req) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$amount, $amount, $count, $count, $req]);
            $packetId = $db->lastInsertId();

            $chatMsg = "🧧 管理员派发了总额 {$amount} 元的红包！";
            $st = $db->prepare("INSERT INTO group_messages (user_id, room_type, message, type, packet_id) VALUES (0, ?, ?, 'red_packet', ?)");
            $st->execute([$room, $chatMsg, $packetId]);
        } else {
            $st = $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (0, ?, ?)");
            $st->execute([$room, $msg]);
        }
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
