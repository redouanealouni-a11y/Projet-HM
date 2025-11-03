<?php

/**
 * Singleton class for managing database connections using PDO.
 */
class Database
{
    /**
     * @var Database|null The single instance of the Database class.
     */
    private static $instance = null;

    /**
     * @var PDO The PDO connection object.
     */
    private $connection;

    /**
     * Private constructor to prevent direct instantiation.
     * Establishes the database connection.
     */
    private function __construct()
    {
        try {
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $this->connection->exec("SET search_path TO public");

        } catch (PDOException $e) {
            // In a real app, you might want a more sophisticated error page.
            // For this API, dying is acceptable for a fatal DB connection error.
            handleError("Erreur de connexion à la base de données : " . $e->getMessage(), 503);
        }
    }

    /**
     * Gets the single instance of the Database class.
     *
     * @return Database The singleton instance.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Gets the raw PDO connection object.
     *
     * @return PDO The PDO connection object.
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Prepares and executes a SQL query.
     *
     * @param string $sql The SQL query to execute.
     * @param array  $params The parameters to bind to the query.
     * @return PDOStatement The prepared statement object.
     * @throws Exception if the query fails in debug mode.
     */
    public function query($sql, $params = [])
    {
        try {
            // Validate SQL is not empty
            if (empty(trim($sql))) {
                throw new Exception("La requête SQL ne peut pas être vide");
            }
            
            // Ensure params is an array
            if (!is_array($params)) {
                throw new Exception("Les paramètres doivent être un tableau");
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $errorMsg = "Erreur de base de données";
            
            if (APP_DEBUG) {
                $errorMsg = "Erreur SQL : " . $e->getMessage() . " | Query: " . $sql;
                error_log($errorMsg);
            }
            
            throw new Exception($errorMsg);
        }
    }

    /**
     * Fetches all rows from a query result.
     *
     * @param string $sql The SQL query.
     * @param array  $params The parameters to bind.
     * @return array An array of all result rows.
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetches a single row from a query result.
     *
     * @param string $sql The SQL query.
     * @param array  $params The parameters to bind.
     * @return mixed The result row, or false if no row is found.
     */
    public function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Executes a statement and returns the number of affected rows.
     *
     * @param string $sql The SQL statement.
     * @param array  $params The parameters to bind.
     * @return int The number of affected rows.
     */
    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Gets the ID of the last inserted row.
     *
     * @param string|null $sequence The name of the sequence object.
     * @return string The ID of the last inserted row.
     */
    public function lastInsertId($sequence = null)
    {
        return $this->connection->lastInsertId($sequence);
    }

    /**
     * Begins a database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commits a database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rolls back a database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }

    /**
     * Checks if the database connection is alive.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected()
    {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Checks if currently in a transaction.
     *
     * @return bool True if in transaction, false otherwise.
     */
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }
}
