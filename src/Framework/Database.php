<?php
namespace Framework;

use PDO;
use PDOException;

class Database {
    private ?PDO $pdo = null;

    public function __construct(
        private string $host,
        private string $dbname,
        private string $user,
        private string $password
    ){}

    public function getConnection(): PDO {
        if (is_null($this->pdo)) {
            $dsn = "mysql:host={$this->host};port=3306;dbname={$this->dbname};charset=utf8";

            try {

                $this->pdo = new PDO(
                    $dsn,
                    $this->user,
                    $this->password,
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

        return $this->pdo;
    }
}