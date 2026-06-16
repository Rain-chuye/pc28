<?php
namespace App\Model;

use App\Utils\DB;
use PDO;

class Lottery {
    public static function getLatest() {
        $db = DB::getInstance()->getConnection();
        return $db->query("SELECT * FROM lottery_results ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    public static function getHistory($limit = 10) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM lottery_results ORDER BY id DESC LIMIT " . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
