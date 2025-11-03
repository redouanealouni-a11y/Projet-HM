<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Manages bank and cash accounts (Comptes).
 * Provides methods for creating, retrieving, updating, and deleting accounts,
 * as well as fetching related statistics and transaction history.
 */
class Compte
{
    /**
     * @var Database The database connection instance.
     */
    private $db;

    /**
     * Compte constructor.
     * Initializes the database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all active accounts, optionally filtered by type.
     *
     * @param string|null $type The type of account to filter by ('caisse' or 'banque').
     * @return array An array of account objects.
     */
    public function getAll($type = null)
    {
        $sql = "SELECT * FROM comptes WHERE is_active = TRUE";
        $params = [];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Retrieves all active accounts with a count of their associated transactions.
     *
     * @return array An array of account objects, each with a 'transaction_count' property.
     */
    public function getAllWithTransactionCount()
    {
        $sql = "
            SELECT
                c.*,
                (SELECT COUNT(*) FROM transactions WHERE account_id = c.id) as transaction_count
            FROM comptes c
            WHERE c.is_active = TRUE
            ORDER BY c.name ASC
        ";
        return $this->db->fetchAll($sql);
    }

    /**
     * Gets the total balance for each account type.
     *
     * @return array An array containing the total balance for 'caisse' and 'banque'.
     */
    public function getTotalBalanceByType()
    {
        $sql = "
            SELECT
                type,
                COALESCE(SUM(balance), 0) as total_balance
            FROM comptes
            WHERE is_active = TRUE
            GROUP BY type
        ";
        return $this->db->fetchAll($sql);
    }

    /**
     * Retrieves a single active account by its UUID.
     *
     * @param string $id The UUID of the account.
     * @return mixed The account object if found, otherwise false.
     */
    public function getById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM comptes WHERE id = ? AND is_active = TRUE",
            [$id]
        );
    }

    /**
     * Searches for active accounts by name, optionally filtered by type.
     *
     * @param string      $query The search query.
     * @param string|null $type  The type of account to filter by.
     * @return array An array of matching account objects.
     */
    public function search($query, $type = null)
    {
        $sql = "SELECT * FROM comptes WHERE name ILIKE ? AND is_active = TRUE";
        $params = ['%' . $query . '%'];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY name ASC LIMIT 10";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Gets transaction statistics for a specific account.
     *
     * @param string $id The UUID of the account.
     * @return array An array containing total transactions, receipts, and expenses.
     */
    public function getStats($id)
    {
        $sql = "
            SELECT
                COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN type = 'recette' THEN amount ELSE 0 END), 0) as total_recettes,
                COALESCE(SUM(CASE WHEN type = 'depense' THEN amount ELSE 0 END), 0) as total_depenses
            FROM transactions
            WHERE account_id = ?
        ";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Retrieves the transaction history for a specific account.
     *
     * @param string $id The UUID of the account.
     * @param int    $limit The maximum number of transactions to retrieve.
     * @return array An array of transaction objects.
     */
    public function getTransactionHistory($id, $limit = 50)
    {
        return $this->db->fetchAll(
            "SELECT * FROM v_transactions_details WHERE account_id = ? ORDER BY date DESC, created_at DESC LIMIT ?",
            [$id, $limit]
        );
    }

    /**
     * Creates a new account.
     *
     * @param array $data The data for the new account. Must contain 'name' and 'type'.
     * @return mixed The newly created account object.
     * @throws Exception if required fields are missing or if the account name already exists.
     */
    public function create($data)
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new Exception("Le nom du compte est obligatoire");
        }

        $sql = "
            INSERT INTO comptes (name, type, balance, bank, description)
            VALUES (?, ?, ?, ?, ?)
            RETURNING *
        ";

        $params = [
            $data['name'],
            $data['type'],
            $data['balance'] ?? 0,
            $data['bank'] ?? null,
            $data['description'] ?? null
        ];

        try {
            return $this->db->fetchOne($sql, $params);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'comptes_name_key') !== false) {
                throw new Exception("Un compte avec ce nom existe déjà.");
            }
            throw $e;
        }
    }

    /**
     * Updates an existing account.
     *
     * @param string $id The UUID of the account to update.
     * @param array  $data The new data for the account.
     * @return mixed The updated account object.
     * @throws Exception if the account is not found or if the new name already exists.
     */
    public function update($id, $data)
    {
        $compte = $this->getById($id);
        if (!$compte) {
            throw new Exception("Compte non trouvé");
        }

        $updates = [];
        $params = [];

        $fields = ['name', 'type', 'balance', 'bank', 'description'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return $compte; // No changes to apply
        }

        $params[] = $id;

        $sql = "UPDATE comptes SET " . implode(', ', $updates) . " WHERE id = ? RETURNING *";

        try {
            return $this->db->fetchOne($sql, $params);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'comptes_name_key') !== false) {
                throw new Exception("Un compte avec ce nom existe déjà.");
            }
            throw $e;
        }
    }

    /**
     * Checks if an account can be safely deleted (i.e., has no transactions).
     *
     * @param string $id The UUID of the account.
     * @return bool True if the account can be deleted, false otherwise.
     */
    public function canDelete($id)
    {
        $count = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM transactions WHERE account_id = ?",
            [$id]
        );
        return $count['count'] == 0;
    }

    /**
     * Deletes an account (soft delete by setting is_active to false).
     *
     * @param string $id The UUID of the account to delete.
     * @return int The number of affected rows.
     * @throws Exception if the account has transactions and cannot be deleted.
     */
    public function delete($id)
    {
        if (!$this->canDelete($id)) {
            throw new Exception("Impossible de supprimer un compte avec des transactions");
        }

        return $this->db->execute(
            "UPDATE comptes SET is_active = FALSE WHERE id = ?",
            [$id]
        );
    }
}
