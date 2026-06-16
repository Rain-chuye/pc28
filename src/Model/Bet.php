<?php
namespace App\Model;

use App\Utils\DB;
use Exception;

class Bet {
    public static function place($userId, $issueNo, $playType, $amount) {
        $db = DB::getInstance();
        $conn = $db->getConnection();
        $conn->beginTransaction();
        try {
            // Check balance
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute(array($userId));
            $balance = $stmt->fetchColumn();

            if ($balance < $amount) {
                throw new Exception("Insufficient balance");
            }

            // Get odds
            $stmt = $conn->prepare("SELECT odds FROM odds_config WHERE play_type = ?");
            $stmt->execute(array($playType));
            $odds = $stmt->fetchColumn();

            if (!$odds) {
                throw new Exception("Invalid play type");
            }

            // Deduct balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute(array($amount, $userId));

            // Insert bet
            $stmt = $conn->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array($userId, $issueNo, $playType, $amount, $odds));

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
