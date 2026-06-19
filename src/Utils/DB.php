<?php
namespace App\Utils;

use PDO;
use Exception;

class DB {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = require __DIR__ . '/../Config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4; SET time_zone = '+08:00'"
            ]);
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // Fallback for Baota common 127.0.0.1 issue
            if ($config['host'] === 'localhost') {
                $dsn_alt = "mysql:host=127.0.0.1;dbname={$config['dbname']};port={$config['port']};charset=utf8mb4";
                $this->connection = new PDO($dsn_alt, $config['user'], $config['password']);
            } else {
                throw $e;
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
