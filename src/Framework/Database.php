<?php
namespace Framework;

use PDO;

class Database {
    private ?PDO $pdo = null;

    public function __construct(
        private string $host,
        private string $dbname,
        private string $user,
        private string $password,
        private string $port
    ){}

    public function getConnection(): PDO {
        if (is_null($this->pdo)) {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";

            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false // stricter with placeholders
                ]
            );
        }

        return $this->pdo;
    }
}