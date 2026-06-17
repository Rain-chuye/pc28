<?php
namespace App\Model;

use App\Utils\DB;
use PDO;

class User {
    public static function getById($id) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(array('id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateBalance($userId, $amount) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");
        return $stmt->execute(array('amount' => $amount, 'id' => $userId));
    }

    public static function addDeposit($userId, $amount) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET balance = balance + :amount, total_deposit = total_deposit + :amount WHERE id = :id");
        return $stmt->execute(array('amount' => $amount, 'id' => $userId));
    }

    public static function addBonus($userId, $amount) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET balance = balance + :amount, total_bonus = total_bonus + :amount WHERE id = :id");
        return $stmt->execute(array('amount' => $amount, 'id' => $userId));
    }

    public static function getAll() {
        $db = DB::getInstance()->getConnection();
        return $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
