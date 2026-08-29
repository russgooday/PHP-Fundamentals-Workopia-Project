<?php
namespace Framework;

use PDO;
use PDOException;

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (is_null(self::$pdo)) {
            // Temporary measure!!
            extract(parse_ini_file('.env'));

            $dsn = "mysql:host={$host};port=3306;dbname={$dbname};charset=utf8";

            try {

                self::$pdo = new PDO(
                    $dsn,
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $err) {

                logError("Database connection failed: " . $err->getMessage());
                die("Database connection failed.");
            }
        }

        return self::$pdo;
    }
}