<?php
class config {
    private static $pdo;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "medicalapp";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

            } catch (PDOException $e) {
                die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}

config::getConnexion();
?>