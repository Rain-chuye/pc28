<?php
namespace App\Model;

use App\Utils\DB;

class Lottery {
    public static function getLatest() {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT * FROM lottery_results ORDER BY open_time DESC LIMIT 1");
        return $stmt->fetch();
    }

    public static function getHistory($limit = 20) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM lottery_results ORDER BY open_time DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
