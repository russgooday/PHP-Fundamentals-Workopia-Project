<?php

namespace Framework\Validation\Model;

use Framework\Database;
use PDO;
use PDOStatement;

class Validations {

    public function __construct(
        private Database $database
    ){}

    protected function getConnection(): PDO {
        return $this->database->getConnection();
    }


    /**
     * Executes a SQL query with optional parameters and returns the PDOStatement.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An associative array of parameters to bind to the query.
     * @return PDOStatement|null The executed PDOStatement or null if the query fails.
     */
    public function query(string $sql, array $params = []): ?PDOStatement {

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }


    public function exists(string $table, string $column, mixed $value) {
        $sql = "
            SELECT * FROM " . quoteIdentifier($table) . "
            WHERE " . quoteIdentifier($column) . " = :value
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':value', $value, pdoDataType($value));
        $stmt->execute();

        return $stmt->fetch();
    }
}