<?php
namespace App\Model;

use App\Utils\DB;

class Bet {
    public static function place($userId, $issueNo, $playType, $amount) {
        $db = DB::getInstance();
        $db->beginTransaction();
        try {
            // Check balance
            $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $balance = $stmt->fetchColumn();

            if ($balance < $amount) {
                throw new \Exception("Insufficient balance");
            }

            // Get odds
            $stmt = $db->prepare("SELECT odds FROM odds_config WHERE play_type = ?");
            $stmt->execute([$playType]);
            $odds = $stmt->fetchColumn();

            if (!$odds) {
                throw new \Exception("Invalid play type");
            }

            // Deduct balance
            $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);

            // Insert bet
            $stmt = $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, bet_amount, odds) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $issueNo, $playType, $amount, $odds]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
