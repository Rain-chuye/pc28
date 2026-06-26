<?php
/**
 * 东爷国际 自动发红包脚本 - 每日凌晨 1 点执行
 */
require_once __DIR__ . '/../src/Utils/DB.php';

$db = \App\Utils\DB::getInstance()->getConnection();

$totalAmount = 1000.00;
$totalCount = 168;
$minTurnover = 100.00; // Requirement from existing schema

try {
    $db->beginTransaction();

    // 1. Create Red Packet
    $stmt = $db->prepare("INSERT INTO red_packets (total_amount, total_count, remaining_amount, remaining_count, min_turnover_req) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$totalAmount, $totalCount, $totalAmount, $totalCount, $minTurnover]);
    $packetId = $db->lastInsertId();

    // 2. Announce in Group Chat
    $msg = "🧧 零点福利！自动发放 $totalAmount 元红包，共 $totalCount 份！名额有限，先到先得！(需流水满 $minTurnover)";
    $stmt = $db->prepare("INSERT INTO group_messages (user_id, message, type, packet_id) VALUES (1, ?, 'red_packet', ?)");
    $stmt->execute([$msg, $packetId]);

    $db->commit();
    echo "Red packet $packetId created successfully.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
