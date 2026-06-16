<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $db = \App\Utils\DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
    $stmt->execute(array('username' => $username));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: /admin/index.php');
        } else {
            header('Location: /index.html');
        }
        die();
    } else {
        echo "<script>alert('Invalid credentials'); window.location.href='/login.html';</script>";
        die();
    }
}
