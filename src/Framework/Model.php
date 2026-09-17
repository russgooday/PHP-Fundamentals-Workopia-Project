<?php
namespace Framework;

use Framework\Database;
use PDO;
use PDOStatement;
use PDOException;

abstract class Model {
    protected string $table;
    protected array $fillable;

    public function __construct(
        private Database $database
    ){}

    protected function getConnection(): PDO {
        return $this->database->getConnection();
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

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }


    /**
     * Fetch all records from the model's table with an optional limit.
     *
     * @param int|null $limit The maximum number of records to fetch. If null, fetch all records.
     * @return array|null An array of records or null if the query fails.
     */
    public function findAll(?int $limit = null): ?array {
        $sql = 'SELECT * FROM ' . $this->getTable() . ' LIMIT :limit';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit ?? PHP_INT_MAX, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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


        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }


    /**
     * Create a new record in the model's table with the provided data.
     *
     * @param array $data The data to insert into the table.
     * @return bool True if the record was successfully created, false otherwise.
     */
    public function create(array $data): bool {
        if (empty($data) || empty($this->fillable)) {
            return false;
        }

        // if no allowed entries found then return false
        if (!($data = filter_by_keys($data, $this->fillable))) {
            return false;
        };

        $cols = array_keys($data);

        $sql = "
            INSERT
                INTO {$this->getTable()} (" . createColumns($cols) . ")
                VALUES (" . createPlaceholders($cols) . ")
        ";


        $stmt = $this->getConnection()->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val, getDataType($val));
        }

        return $stmt->execute();
    }


    /**
     * Delete a record from the model's table by its ID.
     *
     * @param int $id The ID of the record to delete.
     * @return bool True if the record was successfully deleted, false otherwise.
     */
    public function delete(int $id): bool {
        $sql = "
            DELETE FROM {$this->getTable()}
            WHERE id = :id
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        // rowCount will give us a clear indication of whether
        // we are deleting an existing id
        return $stmt->rowCount() > 0;
    }
}