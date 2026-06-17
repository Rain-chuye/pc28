<?php
require_once __DIR__ . '/../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

// This script should be run at 2 AM via cron
$amount = 1000.00;
$count = 500;

$db->beginTransaction();
try {
    // Create red packet
    $stmt = $db->prepare("INSERT INTO red_packets (total_amount, total_count, remaining_amount, remaining_count, min_turnover_req) VALUES (?, ?, ?, ?, 100.00)");
    $stmt->execute([$amount, $count, $amount, $count]);
    $packetId = $db->lastInsertId();

    // Send message to group chat (User ID 1 is Admin)
    $stmt = $db->prepare("INSERT INTO group_messages (user_id, message, type, packet_id) VALUES (1, '今日凌晨红包来啦！金额1000，共500份，名额有限，流水满100即可参与！', 'red_packet', ?)");
    $stmt->execute([$packetId]);

    $db->commit();
    echo "Auto red packet created: ID $packetId\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
