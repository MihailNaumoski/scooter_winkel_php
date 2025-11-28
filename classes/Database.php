<?php
// Database connectie class
class Database {
    private $pdo;

    // Maak connectie bij instantiëren
    public function __construct() {
        require_once __DIR__ . '/../config.php';

        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch(PDOException $e) {
            die("Database connectie mislukt: " . $e->getMessage());
        }
    }

    // Geef PDO object terug
    public function getConnection() {
        return $this->pdo;
    }
}
