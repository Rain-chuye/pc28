<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    die;
}

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    // Exclude large avatar string from general user info to speed up simple lookups
    $stmt = $db->prepare("SELECT id, username, nickname, balance, role, settings_json FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // If the caller explicitly wants the avatar (e.g. profile page)
    if (isset($_GET['include_avatar'])) {
        $st2 = $db->prepare("SELECT avatar FROM users WHERE id = ?");
        $st2->execute([$_SESSION['user_id']]);
        $user['avatar'] = $st2->fetchColumn();
    }

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
