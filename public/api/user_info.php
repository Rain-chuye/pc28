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

// Get count of subs for agent center
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE inviter_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user['sub_count'] = $stmt->fetchColumn();

echo json_encode(['success' => true, 'user' => $user]);
