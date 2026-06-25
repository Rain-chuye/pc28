<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header('Location: /login.html');
    }
    die();
}

// Security: Check if admin is frozen
require_once __DIR__ . '/../../src/Utils/DB.php';
try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetchColumn() === 'frozen') {
        session_destroy();
        header('Location: /login.html?msg=frozen');
        die();
    }
} catch (Exception $e) {}
?>
