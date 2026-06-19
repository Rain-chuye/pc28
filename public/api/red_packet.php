<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'claim') {
    $packetId = (int)$_POST['packet_id'];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT * FROM red_packets WHERE id = ? FOR UPDATE");
        $stmt->execute([$packetId]);
        $packet = $stmt->fetch();

        if (!$packet || $packet['remaining_count'] <= 0) {
            throw new Exception("红包已领完");
        }

        // Check if already claimed
        $stmt = $db->prepare("SELECT id FROM red_packet_claims WHERE packet_id = ? AND user_id = ?");
        $stmt->execute([$packetId, $userId]);
        if ($stmt->fetch()) throw new Exception("您已领取过该红包");

        // Check turnover requirement
        $stmt = $db->prepare("SELECT total_turnover FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn() < $packet['min_turnover_req']) {
            throw new Exception("流水不足 " . $packet['min_turnover_req'] . "，无法领取");
        }

        // Random amount (simple)
        if ($packet['remaining_count'] === 1) {
            $amount = $packet['remaining_amount'];
        } else {
            $max = ($packet['remaining_amount'] / $packet['remaining_count']) * 2;
            $amount = round(mt_rand(10, $max * 100) / 100, 2);
            if ($amount < 0.01) $amount = 0.01;
        }

        // Update packet
        $db->prepare("UPDATE red_packets SET remaining_amount = remaining_amount - ?, remaining_count = remaining_count - 1 WHERE id = ?")
           ->execute([$amount, $packetId]);

        // Insert claim
        $db->prepare("INSERT INTO red_packet_claims (packet_id, user_id, amount) VALUES (?, ?, ?)")
           ->execute([$packetId, $userId, $amount]);

        // Update user balance
        \App\Model\User::updateBalance($userId, $amount, 'red_packet', '领取红包奖励', $db);

        $db->commit();
        echo json_encode(['success' => true, 'amount' => $amount]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
