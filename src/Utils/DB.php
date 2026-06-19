<?php
namespace App\Utils;

use PDO;
use Exception;

class DB {
    private static $instance = null;
    private $connection;

    private function __construct() {
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception("The pdo_mysql extension is required but not installed.");
        }

        $config_file = __DIR__ . '/../Config/database.php';
        if (!file_exists($config_file)) {
            throw new Exception("Database configuration file not found at $config_file");
        }
        $config = require $config_file;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4; SET time_zone = '+08:00'"
        ];

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['password'], $options);
        } catch (Exception $e) {
            // Fallback for Baota common localhost/127.0.0.1 mismatch
            if ($config['host'] === 'localhost' || $config['host'] === '127.0.0.1') {
                $alt_host = ($config['host'] === 'localhost') ? '127.0.0.1' : 'localhost';
                $dsn_alt = "mysql:host=$alt_host;dbname={$config['dbname']};port={$config['port']};charset=utf8mb4";
                try {
                    $this->connection = new PDO($dsn_alt, $config['user'], $config['password'], $options);
                    return;
                } catch (Exception $e2) {
                    throw new Exception("Database Connection Failed (tried both localhost and 127.0.0.1): " . $e2.getMessage());
                }
            }
            throw new Exception("Database Connection Failed: " . $e->getMessage());
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

    /**
     * Verify the connection is still alive
     */
    public function ping() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
