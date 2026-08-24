<?php
require_once __DIR__ . '/../App/helpers/path.php';

require_once basePath('src/App/helpers/debug.php');
require_once basePath('src/App/helpers/logging.php');

class Database {
    private ?PDO $pdo = null;

    public function __construct(
        private array $env
    ) {}

    public function getConnection(): PDO {
        extract($this->env);

        if ($this->pdo === null) {

            $dsn = "mysql:host={$host};port=3306;dbname={$dbname};charset=utf8";

            try {

                $this->pdo = new PDO(
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

        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}
