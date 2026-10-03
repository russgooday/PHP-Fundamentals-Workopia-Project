<?php
namespace Framework;

use PDO;
use PDOException;
use Pdo\Mysql;

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

            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    Mysql::ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES'"
                ]
            );
        }

        return $this->pdo;
    }
}