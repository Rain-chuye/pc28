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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'deposit') {
        $amount = (float)$_POST['amount'];
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => '金额无效']);
            die;
        }

        $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount, proof_img) VALUES (?, 'deposit', ?, 'manual_transfer')");
        $stmt->execute([$userId, $amount]);

        echo json_encode(['success' => true, 'message' => '提交成功，请等待审核']);
    }
} else {
    $stmt = $db->prepare("SELECT * FROM finance_requests WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
