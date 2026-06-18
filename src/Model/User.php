<?php
namespace App\Model;

use App\Utils\DB;
use PDO;
use Exception;

class User {
    public static function getById($id) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(array('id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function logBalance($userId, $type, $amount, $description = '', $conn = null) {
        $db = $conn ?: DB::getInstance()->getConnection();

        // Get current balance
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $balanceBefore = (float)$stmt->fetchColumn();
        $balanceAfter = $balanceBefore + (float)$amount;

        $stmt = $db->prepare("INSERT INTO balance_logs (user_id, type, amount, balance_before, balance_after, description) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $type, $amount, $balanceBefore, $balanceAfter, $description]);
    }

    public static function updateBalance($userId, $amount, $type = 'bonus', $description = '', $conn = null) {
        $db = $conn ?: DB::getInstance()->getConnection();
        $shouldCommit = false;
        if (!$conn) {
            $db->beginTransaction();
            $shouldCommit = true;
        }

        try {
            self::logBalance($userId, $type, $amount, $description, $db);
            $stmt = $db->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");
            $stmt->execute(array('amount' => $amount, 'id' => $userId));

            if ($shouldCommit) $db->commit();
            return true;
        } catch (Exception $e) {
            if ($shouldCommit) $db->rollBack();
            throw $e;
        }
    }

    public static function addDeposit($userId, $amount, $conn = null) {
        $db = $conn ?: DB::getInstance()->getConnection();
        self::updateBalance($userId, $amount, 'deposit', '充值入账', $db);
        $stmt = $db->prepare("UPDATE users SET total_deposit = total_deposit + :amount WHERE id = :id");
        return $stmt->execute(array('amount' => $amount, 'id' => $userId));
    }

    public static function addBonus($userId, $amount, $conn = null) {
        $db = $conn ?: DB::getInstance()->getConnection();
        self::updateBalance($userId, $amount, 'bonus', '福利赠送', $db);
        $stmt = $db->prepare("UPDATE users SET total_bonus = total_bonus + :amount WHERE id = :id");
        return $stmt->execute(array('amount' => $amount, 'id' => $userId));
    }

    public static function getAll() {
        $db = DB::getInstance()->getConnection();
        return $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
