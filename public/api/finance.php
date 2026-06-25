<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($action === 'recharge') {
            $amount = (float)($input['amount'] ?? 0);
            $token = trim($input['token'] ?? '');
            $proof = $input['proof_image'] ?? '';

            if ($amount < 15) {
                echo json_encode(['success' => false, 'message' => '最低充值金额为 15 元']);
                die;
            }
            if (empty($token) && empty($proof)) {
                echo json_encode(['success' => false, 'message' => '请提供口令或凭证']);
                die;
            }

            // Combine token and proof textually for the admin view
            $detail = "";
            if(!empty($token)) $detail .= "口令: " . $token . "\n";
            if(!empty($proof)) $detail .= $proof; // This is the base64

            $stmt = $db->prepare("INSERT INTO finance_requests (user_id, type, amount, proof_img, status) VALUES (?, 'deposit', ?, ?, 'pending')");
            $stmt->execute([$userId, $amount, $detail]);

            echo json_encode(['success' => true]);
            die;
        }
    } else {
        $stmt = $db->prepare("SELECT * FROM finance_requests WHERE user_id = ? ORDER BY id DESC LIMIT 50");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '系统错误: ' . $e->getMessage()]);
}
