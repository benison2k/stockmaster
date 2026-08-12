<?php
namespace App\Core;

use PDO;
use PDOException;

class Model {
    protected $db;

    public function __construct() {
        // Safe check using constant() to prevent static analysis errors
        $host    = defined('DB_HOST')    ? constant('DB_HOST')    : 'localhost';
        $dbname  = defined('DB_NAME')    ? constant('DB_NAME')    : 'stockmaster';
        $user    = defined('DB_USER')    ? constant('DB_USER')    : 'root';
        $pass    = defined('DB_PASS')    ? constant('DB_PASS')    : '';
        $charset = defined('DB_CHARSET') ? constant('DB_CHARSET') : 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->db = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}