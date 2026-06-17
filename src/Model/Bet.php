<?php
namespace App\Model;

use App\Utils\DB;
use Exception;

class Bet {
    public static function place($userId, $issueNo, $playType, $amount, $oddsType = 'low') {
        $db = DB::getInstance();
        $conn = $db->getConnection();
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute(array($userId));
            $balance = $stmt->fetchColumn();

            if ($balance < $amount) throw new Exception("余额不足");

            // Get odds
            $oddsField = $oddsType === 'high' ? 'odds_high' : 'odds_low';
            $stmt = $conn->prepare("SELECT $oddsField FROM odds_config WHERE play_type = ?");
            $stmt->execute(array($playType));
            $odds = $stmt->fetchColumn();

            // If it's a number bet and not explicitly configured, default to 12
            if (!$odds && is_numeric($playType)) {
                $num = (int)$playType;
                if ($num >= 0 && $num <= 27) $odds = 12.00;
            }

            if (!$odds) throw new Exception("无效的玩法");

            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute(array($amount, $userId));

            $stmt = $conn->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(array($userId, $issueNo, $playType, $oddsType, $amount, $odds));

            $stmt = $conn->prepare("UPDATE users SET daily_turnover = daily_turnover + ?, total_turnover = total_turnover + ? WHERE id = ?");
            $stmt->execute(array($amount, $amount, $userId));

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public static function getLiveBets($limit = 20) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT b.*, u.username, u.is_robot FROM bets b JOIN users u ON b.user_id = u.id ORDER BY b.id DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
