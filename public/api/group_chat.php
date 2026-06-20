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
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$roomType = $_GET['room'] ?? 'high';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'send';

    if ($action === 'send') {
        $settings = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'chat_mute_all'")->fetch(PDO::FETCH_KEY_PAIR);
        if (($settings['chat_mute_all'] ?? '0') == '1' && !$isAdmin) {
            echo json_encode(['success' => false, 'message' => '禁言中，仅管理员可发言']);
            die;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $message = trim($data['message'] ?? '');
        if(!$message) { echo json_encode(['success'=>false, 'message'=>'内容不能为空']); die; }

        $stmt = $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $roomType, $message]);

        echo json_encode(['success' => true]);
    } else if ($action === 'claim_red_packet') {
        $data = json_decode(file_get_contents('php://input'), true);
        $packetId = (int)($data['packet_id'] ?? 0);
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT * FROM red_packets WHERE id = ? FOR UPDATE");
            $stmt->execute([$packetId]);
            $packet = $stmt->fetch();
            if (!$packet || $packet['remaining_count'] <= 0) throw new Exception("红包已领完");
            $stmt = $db->prepare("SELECT daily_turnover FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $turnover = $stmt->fetchColumn();
            if ($turnover < $packet['min_turnover_req']) throw new Exception("今日流水不足 " . $packet['min_turnover_req'] . "，无法领取");
            $stmt = $db->prepare("SELECT id FROM red_packet_claims WHERE packet_id = ? AND user_id = ?");
            $stmt->execute([$packetId, $userId]);
            if ($stmt->fetch()) throw new Exception("你已经领过这个红包了");

            $amount = ($packet['remaining_count'] == 1) ? $packet['remaining_amount'] : round(mt_rand(1, ($packet['remaining_amount'] / $packet['remaining_count']) * 2 * 100) / 100, 2);

            $db->prepare("UPDATE red_packets SET remaining_amount = remaining_amount - ?, remaining_count = remaining_count - 1 WHERE id = ?")->execute([$amount, $packetId]);
            $db->prepare("INSERT INTO red_packet_claims (packet_id, user_id, amount) VALUES (?, ?, ?)")->execute([$packetId, $userId, $amount]);
            $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$amount, $userId]);
            $db->commit();
            echo json_encode(['success' => true, 'amount' => $amount]);
        } catch (Exception $e) {
            if($db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} else {
    // GET messages for specific room
    $stmt = $db->prepare("SELECT gm.*, COALESCE(u.nickname, u.username, '系统机器人') as username, u.avatar
                         FROM group_messages gm
                         LEFT JOIN users u ON gm.user_id = u.id
                         WHERE gm.room_type = ?
                         ORDER BY gm.id DESC LIMIT 50");
    $stmt->execute([$roomType]);
    $messages = array_reverse($stmt->fetchAll());
    echo json_encode(['success' => true, 'data' => $messages]);
}
