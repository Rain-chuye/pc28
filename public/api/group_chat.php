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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'];

    if ($action === 'send') {
        $data = json_decode(file_get_contents('php://input'), true);
        $message = $data['message'];
        $stmt = $db->prepare("INSERT INTO group_messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$userId, $message]);
        echo json_encode(['success' => true]);
    } else if ($action === 'claim_red_packet') {
        $packetId = (int)$_POST['packet_id'];

        $db->beginTransaction();
        try {
            // Check packet
            $stmt = $db->prepare("SELECT * FROM red_packets WHERE id = ? FOR UPDATE");
            $stmt->execute([$packetId]);
            $packet = $stmt->fetch();

            if (!$packet || $packet['remaining_count'] <= 0) {
                throw new Exception("红包已领完");
            }

            // Check user turnover
            $stmt = $db->prepare("SELECT daily_turnover FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $turnover = $stmt->fetchColumn();

            if ($turnover < $packet['min_turnover_req']) {
                throw new Exception("今日流水不足100，无法领取");
            }

            // Check if already claimed
            $stmt = $db->prepare("SELECT id FROM red_packet_claims WHERE packet_id = ? AND user_id = ?");
            $stmt->execute([$packetId, $userId]);
            if ($stmt->fetch()) {
                throw new Exception("你已经领过这个红包了");
            }

            // Calculate amount (random)
            if ($packet['remaining_count'] == 1) {
                $amount = $packet['remaining_amount'];
            } else {
                $max = ($packet['remaining_amount'] / $packet['remaining_count']) * 2;
                $amount = round(mt_rand(1, $max * 100) / 100, 2);
            }

            // Update packet
            $db->prepare("UPDATE red_packets SET remaining_amount = remaining_amount - ?, remaining_count = remaining_count - 1 WHERE id = ?")
               ->execute([$amount, $packetId]);

            // Insert claim
            $db->prepare("INSERT INTO red_packet_claims (packet_id, user_id, amount) VALUES (?, ?, ?)")
               ->execute([$packetId, $userId, $amount]);

            // Update balance
            $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
               ->execute([$amount, $userId]);

            $db->commit();
            echo json_encode(['success' => true, 'amount' => $amount]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} else {
    // GET: load group messages
    $stmt = $db->prepare("SELECT gm.*, u.username FROM group_messages gm JOIN users u ON gm.user_id = u.id ORDER BY gm.id DESC LIMIT 50");
    $stmt->execute();
    $messages = array_reverse($stmt->fetchAll());
    echo json_encode(['success' => true, 'data' => $messages]);
}
