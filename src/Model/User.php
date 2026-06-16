<?php
namespace App\Model;

use App\Utils\DB;

class User {
    public static function register($username, $password) {
        $db = DB::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $hash]);
    }

    public static function login($username, $password) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public static function getBalance($userId) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public static function updateBalance($userId, $amount) {
        $db = DB::getInstance();
        $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        return $stmt->execute([$amount, $userId]);
    }
}
