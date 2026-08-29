<?php
namespace Framework;

use Framework\Database;
use PDO;
use PDOStatement;
use PDOException;

abstract class Model {

    protected function getConnection(): PDO {
        return Database::getConnection();
    }


    protected function getClassName(): string {
        return strtolower(array_last(explode('\\', $this::class)));
    }


    /**
     * If defined returns the '$this->table' name,
     * otherwise the class name in lowercase.
     * @return string The table name.
     */
    public function getTable(): string {
        return $this->table ?? $this->getClassName();
    }


    /**
     * Executes a SQL query with optional parameters and returns the PDOStatement.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An associative array of parameters to bind to the query.
     * @return PDOStatement|null The executed PDOStatement or null if the query fails.
     */
    public function query(string $sql, array $params = []): ?PDOStatement {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (PDOException $err) {

            logError("query failed to execute: " . $err->getMessage());
            return null;
        }
    }


    /**
     * Fetch all records from the model's table with an optional limit.
     *
     * @param int|null $limit The maximum number of records to fetch. If null, fetch all records.
     * @return array|null An array of records or null if the query fails.
     */
    public function findAll(?int $limit = null): ?array {
        $sql = 'SELECT * FROM ' . $this->getTable() . ' LIMIT :limit';

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit ?? PHP_INT_MAX, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $err) {

            logError("findAll query failed to execute: " . $err->getMessage());
            return null;
        }
    }


    /**
     * Fetch a single record from the model's table by its ID.
     *
     * @param int $id The ID of the record to fetch.
     * @return mixed The record as an associative array, or
     * null if not found or the query fails.
     */
    public function findOne(int $id): mixed {
        $sql = 'SELECT * FROM ' . $this->getTable() . ' WHERE id = :id';

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $err) {

            logError("findOne query failed to execute: " . $err->getMessage());
            return null;
        }
    }
}