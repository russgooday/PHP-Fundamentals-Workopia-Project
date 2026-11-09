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

    /**
     * Checks if a value exists in a specific column of a table.
     *
     * @param string $table The name of the table to check.
     * @param string $column The column to check the value against.
     * @param mixed $value The value to check for existence.
     * @return bool True if the value exists, false otherwise.
     */
    public function exists(string $table, string $column, mixed $value): bool {
        $sql = "
            SELECT 1 FROM " . quoteIdentifier($table) . "
            WHERE " . quoteIdentifier($column) . " = :value
            LIMIT 1
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':value', $value, pdoDataType($value));
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}