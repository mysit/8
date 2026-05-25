<?php
namespace App\Services;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/db.php';
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s",
                $config['host'], $config['dbname'], $config['charset']);
            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['status'=>'error','message'=>'DB connection failed']);
                exit;
            }
        }
        return self::$instance;
    }
}
