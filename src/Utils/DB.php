<?php
namespace App\Utils;

use PDO;
use PDOException;

class DB {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../Config/database.php';
            try {
                // For this environment, we might need to handle the missing driver.
                // In production, this would be pgsql.
                $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']};port={$config['port']}";
                self::$instance = new PDO($dsn, $config['user'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // Log or handle error
                throw $e;
            }
        }
        return self::$instance;
    }
}
