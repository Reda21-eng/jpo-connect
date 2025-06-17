<?php
// ...existing code...
class Database {
    private static ?\PDO $instance = null;

    public static function getInstance(): \PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=localhost;dbname=jpo-connect;charset=utf8mb4"; // Correction du nom de la base de données
            self::$instance = new \PDO($dsn, "root", "", [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        }
        return self::$instance;
    }
}
// ...existing code...
