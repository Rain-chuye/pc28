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
$data = json_decode(file_get_contents('php://input'), true);

try {
    $user = \App\Model\User::getById($userId);

    if (isset($data['password']) && !empty($data['password'])) {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$data['password'], $userId]);
    }

    if (isset($data['qq']) && !empty($data['qq'])) {
        $stmt = $db->prepare("UPDATE users SET qq_number = ? WHERE id = ?");
        $stmt->execute([$data['qq'], $userId]);
    }

    if (isset($data['nickname']) && !empty($data['nickname'])) {
        $stmt = $db->prepare("UPDATE users SET nickname = ? WHERE id = ?");
        $stmt->execute([trim($data['nickname']), $userId]);
    }

    if (isset($data['theme_settings'])) {
        $stmt = $db->prepare("UPDATE users SET settings_json = ? WHERE id = ?");
        $stmt->execute([json_encode($data['theme_settings']), $userId]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
