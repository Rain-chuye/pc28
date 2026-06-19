<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$user = \App\Model\User::getById($_SESSION['user_id']);

if ($user) {
    // Get count of subs for agent center
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE inviter_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user['sub_count'] = $stmt->fetchColumn();

    // Ensure password is not exposed too much, but needed for admin edit in some views
    // Actually User::getById returns everything. For security we might want to mask it.
    // However, the admin needs to see/edit it in users.php.
}

echo json_encode(['success' => true, 'user' => $user]);
