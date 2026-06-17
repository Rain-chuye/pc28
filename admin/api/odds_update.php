<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$db = \App\Utils\DB::getInstance()->getConnection();

$db->beginTransaction();
try {
    foreach ($data['updates'] as $u) {
        $stmt = $db->prepare("UPDATE odds_config SET odds_low = ?, odds_high = ? WHERE id = ?");
        $stmt->execute([$u['odds_low'], $u['odds_high'], $u['id']]);
    }
    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
