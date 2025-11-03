<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Manages all financial transactions.
 * Handles creation, retrieval, deletion, and statistics for receipts,
 * expenses, and transfers between accounts.
 */
class Transaction
{
    /**
     * @var Database The database connection instance.
     */
    private $db;

    /**
     * Transaction constructor.
     * Initializes the database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new standard transaction (receipt or expense).
     *
     * @param array $data The data for the new transaction.
     * @return mixed The newly created transaction object, fully detailed.
     * @throws Exception if required fields are missing or data is invalid.
     */
    public function create($data)
    {
        // ✅ VALIDATION DIFFÉRENCIÉE - ACCOUNT_ID NON OBLIGATOIRE POUR LES ACHATS
        if ($data['type'] === 'achat') {
            // Pour les achats: only type, description, amount are required
            $errors = validateRequired($data, ['type', 'description', 'amount']);
            $requiredFieldsText = 'type, description, amount';
        } else {
            // Pour les autres transactions: account_id is required
            $errors = validateRequired($data, ['type', 'description', 'amount', 'account_id']);
            $requiredFieldsText = 'type, description, amount, account_id';
        }
        
        if (!empty($errors)) {
            throw new Exception("Champs requis manquants: " . $errors[0]);
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception("Le montant doit être un nombre positif");
        }

        // ✅ GESTION DES TYPES AVEC OU SANS ACCOUNT_ID
        if ($data['type'] === 'achat') {
            $validTypes = ['achat'];
        } else {
            $validTypes = ['recette', 'depense'];
        }
        
        if (!in_array($data['type'], $validTypes)) {
            throw new Exception("Type de transaction invalide pour une création simple.");
        }

        $this->db->beginTransaction();
        try {
            // ✅ SEULEMENT si account_id est fourni (optionnel pour les achats)
            if (!empty($data['account_id'])) {
                $compte = $this->db->fetchOne("SELECT * FROM comptes WHERE id = ? AND is_active = TRUE", [$data['account_id']]);
                if (!$compte) {
                    throw new Exception("Compte non trouvé");
                }

                $newBalance = $this->calculateNewBalance($compte, $data['type'], $data['amount']);
                $transactionId = $this->insertTransaction($data, $newBalance);
                $this->updateAccountBalance($data['account_id'], $newBalance);
            } else {
                // ✅ PAS DE COMPTE SÉLECTIONNÉ - Création sans impact sur le solde
                $transactionId = $this->insertTransaction($data, 0);
            }

            $this->db->commit();
            return $this->getById($transactionId);
        } catch (Exception $e) {
            // Ne faire un rollback que si une transaction est active
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Creates a transfer between two accounts, which consists of two linked transactions.
     *
     * @param string $fromAccountId The UUID of the source account.
     * @param string $toAccountId The UUID of the destination account.
     * @param float  $amount The amount to transfer.
     * @param string $description A description for the transfer.
     * @return array An array containing the debit and credit transaction objects.
     * @throws Exception if accounts are invalid, the same, or the source balance is insufficient.
     */
    public function createTransfer($fromAccountId, $toAccountId, $amount, $description = 'Virement de fonds', $extraData = [])
    {
        if ($fromAccountId === $toAccountId) {
            throw new Exception("Impossible de faire un virement vers le même compte");
        }
        if (!is_numeric($amount) || $amount <= 0) {
            throw new Exception("Le montant du virement doit être un nombre positif");
        }

        $this->db->beginTransaction();
        try {
            $fromAccount = $this->db->fetchOne("SELECT * FROM comptes WHERE id = ? AND is_active = TRUE", [$fromAccountId]);
            $toAccount = $this->db->fetchOne("SELECT * FROM comptes WHERE id = ? AND is_active = TRUE", [$toAccountId]);

            if (!$fromAccount || !$toAccount) {
                throw new Exception("Un ou plusieurs comptes sont introuvables");
            }
            if ($fromAccount['balance'] < $amount) {
                throw new Exception("Solde insuffisant sur le compte source");
            }

            // A single transfer is represented by two linked transactions.
            $transferRef = $this->db->fetchOne("SELECT uuid_generate_v4() as uuid")['uuid'];

            // ✨ Préparer les données de base
            $baseData = [
                'amount' => $amount,
                'date' => $extraData['date'] ?? date('Y-m-d'),
                'transfer_ref' => $transferRef,
                // Onglet 2: Infos bancaires
                'reference' => $extraData['reference'] ?? null,
                'payment_method' => $extraData['payment_method'] ?? null,
                'value_date' => $extraData['value_date'] ?? null,
                'effective_date' => $extraData['execution_date'] ?? null,
                'bank_status' => $extraData['status'] ?? 'pending',
                'bank_notes' => $extraData['bank_notes'] ?? null,
                // Onglet 4: Gestion & Actions
                'general_comments' => $extraData['general_comments'] ?? null
            ];

            $debitData = array_merge($baseData, [
                'type' => 'virement_debit',
                'description' => $description . ' (vers ' . $toAccount['name'] . ')',
                'account_id' => $fromAccountId
            ]);
            
            $creditData = array_merge($baseData, [
                'type' => 'virement_credit',
                'description' => $description . ' (de ' . $fromAccount['name'] . ')',
                'account_id' => $toAccountId
            ]);

            $newFromBalance = $fromAccount['balance'] - $amount;
            $newToBalance = $toAccount['balance'] + $amount;

            $debitId = $this->insertTransaction($debitData, $newFromBalance);
            $creditId = $this->insertTransaction($creditData, $newToBalance);

            // 🔗 Créer les liens croisés entre les transactions
            $this->db->execute("UPDATE transactions SET credit_transaction_id = ? WHERE id = ?", [$creditId, $debitId]);
            $this->db->execute("UPDATE transactions SET debit_transaction_id = ? WHERE id = ?", [$debitId, $creditId]);

            $this->updateAccountBalance($fromAccountId, $newFromBalance);
            $this->updateAccountBalance($toAccountId, $newToBalance);

            $this->db->commit();

            return ['debit' => $this->getById($debitId), 'credit' => $this->getById($creditId)];
        } catch (Exception $e) {
            // Ne faire un rollback que si une transaction est active
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Retrieves all transactions, with filtering and pagination.
     *
     * @param array $filters An associative array of filters (e.g., 'search', 'type', 'limit').
     * @return array An array of transaction objects.
     */
    public function getAll($filters = [])
    {
        // URGENT: Utiliser directement la requête directe pour corriger les account_id manquants
        // La vue v_transactions_details a des problèmes avec les virements
        return $this->getAllFromTables($filters);
    }

    /**
     * Récupère toutes les transactions liées à un transfert spécifique
     */
    public function getByTransferRef($transferRef)
    {
        try {
            $sql = "SELECT * FROM transactions WHERE transfer_ref = ? ORDER BY created_at";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$transferRef]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des transactions par transfer_ref: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère les transactions en utilisant la vue v_transactions_details
     */
    private function getAllFromView($filters = [])
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(description ILIKE ? OR reference ILIKE ? OR tiers_name ILIKE ? OR category_name ILIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if (!empty($filters['type'])) {
            $where[] = "type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['account_id'])) {
            $where[] = "account_id = ?";
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['tiers_id'])) {
            $where[] = "tiers_id = ?";
            $params[] = $filters['tiers_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['month'])) {
            $where[] = "EXTRACT(MONTH FROM date) = ? AND EXTRACT(YEAR FROM date) = EXTRACT(YEAR FROM CURRENT_DATE)";
            $params[] = $filters['month'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "date <= ?";
            $params[] = $filters['date_to'];
        }

        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        $params[] = $limit;

        $sql = "SELECT * FROM v_transactions_details WHERE " . implode(' AND ', $where) . " ORDER BY date DESC, created_at DESC LIMIT ?";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère les transactions en utilisant directement les tables (solution de contournement)
     */
    private function getAllFromTables($filters = [])
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(t.description ILIKE ? OR t.reference ILIKE ? OR tiers.raison_sociale ILIKE ? OR cat.name ILIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if (!empty($filters['type'])) {
            $where[] = "t.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['account_id'])) {
            $where[] = "t.account_id = ?";
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['tiers_id'])) {
            $where[] = "t.tiers_id = ?";
            $params[] = $filters['tiers_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['month'])) {
            $where[] = "EXTRACT(MONTH FROM t.date) = ? AND EXTRACT(YEAR FROM t.date) = EXTRACT(YEAR FROM CURRENT_DATE)";
            $params[] = $filters['month'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "t.date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "t.date <= ?";
            $params[] = $filters['date_to'];
        }

        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        $params[] = $limit;

        $sql = "SELECT 
                    t.id,
                    t.type,
                    t.description,
                    t.amount,
                    t.date,
                    t.balance_after,
                    t.transfer_ref,
                    t.notes,
                    t.reference,
                    t.payment_method,
                    t.bank_status,
                    t.value_date,
                    t.created_at,
                    t.account_id,
                    t.tiers_id,
                    t.category_id,
                    c.name as account_name,
                    c.type as account_type,
                    tiers.raison_sociale as tiers_name,
                    cat.name as category_name
                FROM transactions t
                LEFT JOIN comptes c ON t.account_id = c.id
                LEFT JOIN tiers ON t.tiers_id = tiers.id
                LEFT JOIN categories cat ON t.category_id = cat.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.date DESC, t.created_at DESC 
                LIMIT ?";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Retrieves a single transaction by its UUID, using the detailed view.
     *
     * @param string $id The UUID of the transaction.
     * @return mixed The transaction object if found, otherwise false.
     */
    public function getById($id)
    {
        // Utiliser une requête directe pour s'assurer que tous les champs sont inclus
        $sql = "SELECT 
                    t.id,
                    t.type,
                    t.description,
                    t.amount,
                    t.date,
                    t.balance_after,
                    t.transfer_ref,
                    t.notes,
                    t.reference,
                    t.payment_method,
                    t.bank_status,
                    t.value_date,
                    t.due_date,
                    t.effective_date,
                    t.balance_impact,
                    t.bank_notes,
                    t.general_comments,
                    t.created_at,
                    t.updated_at,
                    t.account_id,
                    t.tiers_id,
                    t.category_id,
                    c.name as account_name,
                    c.type as account_type,
                    tiers.raison_sociale as tiers_name,
                    cat.name as category_name
                FROM transactions t
                LEFT JOIN comptes c ON t.account_id = c.id
                LEFT JOIN tiers ON t.tiers_id = tiers.id
                LEFT JOIN categories cat ON t.category_id = cat.id
                WHERE t.id = ?";
        
        $transaction = $this->db->fetchOne($sql, [$id]);
        
        // Récupérer les documents associés
        if ($transaction) {
            $transaction['documents'] = $this->getTransactionDocuments($id);
        }
        
        return $transaction;
    }

    /**
     * Deletes a transaction and correctly reverts the balance of the associated account(s).
     * If the transaction is part of a transfer, both linked transactions are deleted.
     *
     * @param string $id The UUID of the transaction to delete.
     * @return bool True on success.
     * @throws Exception if the transaction is not found.
     */
    public function delete($id)
    {
        $transaction = $this->db->fetchOne("SELECT * FROM transactions WHERE id = ?", [$id]);
        if (!$transaction) {
            throw new Exception("Transaction non trouvée");
        }

        $this->db->beginTransaction();
        try {
            if (!empty($transaction['transfer_ref'])) {
                // It's a transfer, revert both transactions and delete them.
                $linkedTxs = $this->db->fetchAll("SELECT * FROM transactions WHERE transfer_ref = ?", [$transaction['transfer_ref']]);
                foreach ($linkedTxs as $linkedTx) {
                    $this->revertTransactionBalance($linkedTx);
                }
                $this->db->execute("DELETE FROM transactions WHERE transfer_ref = ?", [$transaction['transfer_ref']]);
            } else {
                // It's a simple transaction.
                $this->revertTransactionBalance($transaction);
                $this->db->execute("DELETE FROM transactions WHERE id = ?", [$id]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Ne faire un rollback que si une transaction est active
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Retrieves transaction statistics, with optional date filtering.
     * Includes logic to handle and log abnormally large values.
     *
     * @param array $filters An associative array of filters (e.g., 'date_from', 'date_to').
     * @return array An array of statistics.
     */
    public function getStats($filters = [])
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "date <= ?";
            $params[] = $filters['date_to'];
        }

        $baseQuery = "FROM transactions WHERE " . implode(' AND ', $where);

        try {
            $stats_query = "SELECT COUNT(*) as total_transactions,
                                   COALESCE(SUM(CASE WHEN type IN ('recette', 'virement_credit') THEN amount ELSE 0 END), 0) as total_recettes,
                                   COALESCE(SUM(CASE WHEN type IN ('depense', 'virement_debit') THEN amount ELSE 0 END), 0) as total_depenses
                            " . $baseQuery;

            $stats = $this->db->fetchOne($stats_query, $params);

            $total_transactions = max(0, intval($stats['total_transactions'] ?? 0));
            $total_recettes = max(0, floatval($stats['total_recettes'] ?? 0));
            $total_depenses = max(0, floatval($stats['total_depenses'] ?? 0));

            // Handle and log abnormally large values to prevent issues.
            if ($total_recettes > 100000000 || $total_depenses > 100000000) {
                error_log("ATTENTION: Valeurs de stats anormales détectées - Recettes: $total_recettes, Dépenses: $total_depenses");

                $safe_stats_query = "SELECT COALESCE(SUM(CASE WHEN type = 'recette' AND amount <= 1000000 THEN amount ELSE 0 END), 0) as total_recettes,
                                            COALESCE(SUM(CASE WHEN type = 'depense' AND amount <= 1000000 THEN amount ELSE 0 END), 0) as total_depenses
                                     " . $baseQuery;
                $stats_safe = $this->db->fetchOne($safe_stats_query, $params);
                $total_recettes = floatval($stats_safe['total_recettes']);
                $total_depenses = floatval($stats_safe['total_depenses']);
            }

            return compact('total_transactions', 'total_recettes', 'total_depenses');
        } catch (Exception $e) {
            error_log("Erreur stats: " . $e->getMessage());
            return ['total_transactions' => 0, 'total_recettes' => 0, 'total_depenses' => 0];
        }
    }

    /**
     * Calculates the new balance of an account after a transaction.
     *
     * @param array  $compte The account object.
     * @param string $type The transaction type.
     * @param float  $amount The transaction amount.
     * @return float The calculated new balance.
     * @throws Exception if the transaction type is invalid.
     */
    private function calculateNewBalance($compte, $type, $amount)
    {
        switch ($type) {
            case 'recette':
            case 'virement_credit':
                return $compte['balance'] + $amount;
            case 'depense':
            case 'virement_debit':
                return $compte['balance'] - $amount;
            default:
                throw new Exception("Type de transaction invalide pour le calcul du solde");
        }
    }

    /**
     * Inserts a new transaction record into the database.
     *
     * @param array $data The transaction data.
     * @param float $balanceAfter The new balance of the account after this transaction.
     * @return string The UUID of the newly inserted transaction.
     */
    private function insertTransaction($data, $balanceAfter)
    {
        $sql = "
            INSERT INTO transactions (id, type, description, amount, date, account_id, tiers_id, category_id, balance_after, transfer_ref, notes, reference, payment_method, bank_status, value_date, due_date, effective_date, balance_impact, bank_notes, general_comments)
            VALUES (uuid_generate_v4(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id";

        // Convertir les chaînes vides en NULL pour les champs UUID optionnels et autres champs optionnels
        $tiersId = isset($data['tiers_id']) && $data['tiers_id'] !== '' ? $data['tiers_id'] : null;
        $categoryId = isset($data['category_id']) && $data['category_id'] !== '' ? $data['category_id'] : null;
        $transferRef = isset($data['transfer_ref']) && $data['transfer_ref'] !== '' ? $data['transfer_ref'] : null;
        $paymentMethod = isset($data['payment_method']) && $data['payment_method'] !== '' ? $data['payment_method'] : null;
        $bankStatus = isset($data['bank_status']) && $data['bank_status'] !== '' ? $data['bank_status'] : null;
        $valueDate = isset($data['value_date']) && $data['value_date'] !== '' ? $data['value_date'] : null;
        $dueDate = isset($data['due_date']) && $data['due_date'] !== '' ? $data['due_date'] : null;
        $effectiveDate = isset($data['effective_date']) && $data['effective_date'] !== '' ? $data['effective_date'] : null;
        $balanceImpact = isset($data['balance_impact']) && $data['balance_impact'] !== '' ? $data['balance_impact'] : null;
        $bankNotes = isset($data['bank_notes']) && $data['bank_notes'] !== '' ? $data['bank_notes'] : null;
        $generalComments = isset($data['general_comments']) && $data['general_comments'] !== '' ? $data['general_comments'] : null;

        $params = [
            $data['type'], $data['description'], $data['amount'],
            $data['date'] ?? date('Y-m-d'),
            $data['account_id'], $tiersId, $categoryId,
            $balanceAfter, $transferRef, $data['notes'] ?? null, $data['reference'] ?? null,
            $paymentMethod, $bankStatus, $valueDate, $dueDate, $effectiveDate, $balanceImpact, $bankNotes, $generalComments
        ];

        $result = $this->db->fetchOne($sql, $params);
        return $result['id'];
    }

    /**
     * Updates the balance of a specific account.
     *
     * @param string $accountId The UUID of the account.
     * @param float  $newBalance The new balance to set.
     */
    private function updateAccountBalance($accountId, $newBalance)
    {
        $this->db->execute("UPDATE comptes SET balance = ? WHERE id = ?", [$newBalance, $accountId]);
    }

    /**
     * Reverts the balance of an account based on a transaction being deleted.
     *
     * @param array $transaction The transaction being deleted.
     * @throws Exception if the associated account is not found.
     */
    private function revertTransactionBalance($transaction)
    {
        $compte = $this->db->fetchOne("SELECT * FROM comptes WHERE id = ?", [$transaction['account_id']]);
        if (!$compte) {
            throw new Exception("Compte associé à la transaction non trouvé lors de l'annulation.");
        }

        $newBalance = 0;
        // The logic is reversed for reversion.
        if ($transaction['type'] === 'recette' || $transaction['type'] === 'virement_credit') {
            $newBalance = $compte['balance'] - $transaction['amount'];
        } else {
            $newBalance = $compte['balance'] + $transaction['amount'];
        }
        $this->updateAccountBalance($transaction['account_id'], $newBalance);
    }

    /**
     * Updates an existing transaction and correctly recalculates all subsequent balances.
     *
     * @param string $id The UUID of the transaction to update.
     * @param array  $data The new data for the transaction.
     * @return mixed The updated transaction object.
     * @throws Exception if the transaction is not found or data is invalid.
     */
    public function update($id, $data)
    {
        $this->db->beginTransaction();
        try {
            $original = $this->db->fetchOne("SELECT * FROM transactions WHERE id = ?", [$id]);
            if (!$original) {
                throw new Exception("Transaction non trouvée.");
            }
            
            // 🔄 Si c'est un virement, synchroniser automatiquement avec la transaction liée
            if (!empty($original['transfer_ref'])) {
                return $this->updateLinkedTransfer($original, $data);
            }

            $oldAccountId = $original['account_id'];
            $newAccountId = $data['account_id'] ?? $oldAccountId;

            // Step 1: Revert all balances on the old account from the transaction date forward.
            $this->recalculateBalancesForAccount($oldAccountId, $original['date']);

            // Step 2: If the account has changed, also revert balances on the new account.
            if ($oldAccountId !== $newAccountId) {
                $this->recalculateBalancesForAccount($newAccountId, $original['date']);
            }

            // Step 3: Update the transaction record itself.
            $updates = [];
            $params = [];
            $fields = ['type', 'description', 'amount', 'date', 'account_id', 'tiers_id', 'category_id', 'notes', 'reference', 'payment_method', 'bank_status', 'value_date', 'due_date', 'effective_date', 'balance_impact', 'bank_notes', 'general_comments'];
            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "$field = ?";
                    
                    // Convertir les chaînes vides en NULL pour les champs UUID optionnels et autres champs optionnels
                    $value = $data[$field];
                    if (in_array($field, ['tiers_id', 'category_id', 'payment_method', 'bank_status', 'value_date', 'due_date', 'effective_date', 'balance_impact', 'bank_notes', 'general_comments']) && ($value === '' || $value === null)) {
                        $value = null;
                    }
                    
                    $params[] = $value;
                }
            }
            $params[] = $id;
            $sql = "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ?";
            $this->db->execute($sql, $params);

            // Step 4: Recalculate all balances from the transaction date forward on the new account.
            $this->recalculateBalancesForAccount($newAccountId, $data['date'] ?? $original['date']);

            $this->db->commit();
            return $this->getById($id);

        } catch (Exception $e) {
            // Ne faire un rollback que si une transaction est active
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Met à jour un virement en synchronisant automatiquement avec la transaction liée
     */
    private function updateLinkedTransfer($originalTransaction, $data)
    {
        // 🔍 Trouver la transaction liée (crédit ou débit)
        $linkedTransaction = null;
        
        if ($originalTransaction['type'] === 'virement_debit') {
            // Transaction débit → chercher transaction crédit
            $linkedTransaction = $this->db->fetchOne(
                "SELECT * FROM transactions WHERE transfer_ref = ? AND type = 'virement_credit'",
                [$originalTransaction['transfer_ref']]
            );
        } elseif ($originalTransaction['type'] === 'virement_credit') {
            // Transaction crédit → chercher transaction débit
            $linkedTransaction = $this->db->fetchOne(
                "SELECT * FROM transactions WHERE transfer_ref = ? AND type = 'virement_debit'",
                [$originalTransaction['transfer_ref']]
            );
        }
        
        if (!$linkedTransaction) {
            throw new Exception("Transaction liée non trouvée pour la synchronisation.");
        }
        
        // 🔄 Préparer les données pour la transaction liée
        $linkedData = $this->prepareLinkedTransferData($originalTransaction, $linkedTransaction, $data);
        
        // ✏️ Mettre à jour la transaction originale
        $this->updateSingleTransaction($originalTransaction['id'], $data, $originalTransaction);
        
        // 🔗 Mettre à jour la transaction liée avec les données synchronisées
        $this->updateSingleTransaction($linkedTransaction['id'], $linkedData, $linkedTransaction);
        
        $this->db->commit();
        
        return $this->getById($originalTransaction['id']);
    }
    
    /**
     * Prépare les données synchronisées pour la transaction liée
     */
    private function prepareLinkedTransferData($originalTransaction, $linkedTransaction, $newData)
    {
        $linkedData = [];
        
        // 🔄 Champs à synchroniser (mêmes valeurs)
        $syncFields = ['date', 'reference', 'payment_method', 'value_date', 'effective_date', 'bank_status', 'bank_notes', 'general_comments'];
        foreach ($syncFields as $field) {
            if (array_key_exists($field, $newData)) {
                $linkedData[$field] = $newData[$field];
            }
        }
        
        // 💰 Montant (opposé pour la transaction liée)
        if (array_key_exists('amount', $newData)) {
            $linkedData['amount'] = -abs($newData['amount']);
        }
        
        // 🏷️ Description (adaptée selon le type)
        if (array_key_exists('description', $newData)) {
            $baseDescription = preg_replace('/\s*\(.*?\)\s*$/', '', $newData['description']);
            if ($linkedTransaction['type'] === 'virement_credit') {
                $linkedData['description'] = $baseDescription . ' (de ' . $this->getAccountName($originalTransaction['account_id']) . ')';
            } elseif ($linkedTransaction['type'] === 'virement_debit') {
                $linkedData['description'] = $baseDescription . ' (vers ' . $this->getAccountName($originalTransaction['account_id']) . ')';
            }
        }
        
        // 🏦 Compte (changement de compte = synchronisation)
        if (array_key_exists('account_id', $newData)) {
            if ($originalTransaction['type'] === 'virement_debit') {
                // Si débit change de compte → crédit garde son compte
                // Mais on met à jour la balance du nouveau compte de débit
                $this->updateAccountAfterTransfer($originalTransaction, $newData, $linkedTransaction);
            } elseif ($originalTransaction['type'] === 'virement_credit') {
                // Si crédit change de compte → débit garde son compte
                $this->updateAccountAfterTransfer($linkedTransaction, $newData, $originalTransaction);
            }
        }
        
        return $linkedData;
    }
    
    /**
     * Met à jour une seule transaction (sans synchronisation)
     */
    private function updateSingleTransaction($id, $data, $original)
    {
        $oldAccountId = $original['account_id'];
        $newAccountId = $data['account_id'] ?? $oldAccountId;
        
        // Recalculer les soldes si nécessaire
        if ($oldAccountId !== $newAccountId || array_key_exists('date', $data) || array_key_exists('amount', $data)) {
            $this->recalculateBalancesForAccount($oldAccountId, $original['date']);
            if ($oldAccountId !== $newAccountId) {
                $this->recalculateBalancesForAccount($newAccountId, $original['date']);
            }
        }
        
        // Mettre à jour la transaction
        $updates = [];
        $params = [];
        $fields = ['type', 'description', 'amount', 'date', 'account_id', 'tiers_id', 'category_id', 'notes', 'reference', 'payment_method', 'bank_status', 'value_date', 'due_date', 'effective_date', 'balance_impact', 'bank_notes', 'general_comments'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                
                $value = $data[$field];
                if (in_array($field, ['tiers_id', 'category_id', 'payment_method', 'bank_status', 'value_date', 'due_date', 'effective_date', 'balance_impact', 'bank_notes', 'general_comments']) && ($value === '' || $value === null)) {
                    $value = null;
                }
                
                $params[] = $value;
            }
        }
        $params[] = $id;
        $sql = "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->execute($sql, $params);
        
        // Recalculer les soldes après modification
        if ($oldAccountId !== $newAccountId || array_key_exists('date', $data) || array_key_exists('amount', $data)) {
            $this->recalculateBalancesForAccount($newAccountId, $data['date'] ?? $original['date']);
        }
    }
    
    /**
     * Obtient le nom d'un compte par son ID
     */
    private function getAccountName($accountId)
    {
        $account = $this->db->fetchOne("SELECT name FROM comptes WHERE id = ?", [$accountId]);
        return $account ? $account['name'] : 'Compte inconnu';
    }
    
    /**
     * Met à jour les soldes de compte après modification d'un transfert
     */
    private function updateAccountAfterTransfer($debitTransaction, $creditData, $creditTransaction)
    {
        // Cette méthode peut être étendue pour gérer des changements de comptes complexes
        // Pour l'instant, on recalcule les soldes des deux comptes impliqués
        $this->recalculateBalancesForAccount($debitTransaction['account_id'], $debitTransaction['date']);
        if (isset($creditData['account_id'])) {
            $this->recalculateBalancesForAccount($creditData['account_id'], $creditTransaction['date']);
        }
    }

    /**
     * Recalculates all transaction balances for a specific account from a given date.
     *
     * @param string $accountId The UUID of the account.
     * @param string $fromDate The start date for recalculation (Y-m-d).
     */
    private function recalculateBalancesForAccount($accountId, $fromDate)
    {
        // Get the account information
        $compte = $this->db->fetchOne("SELECT * FROM comptes WHERE id = ?", [$accountId]);
        if (!$compte) {
            throw new Exception("Compte non trouvé pour la recalculation des soldes");
        }

        // Get the balance of the last transaction *before* the fromDate.
        $lastTxBefore = $this->db->fetchOne(
            "SELECT balance_after FROM transactions WHERE account_id = ? AND date < ? ORDER BY date DESC, created_at DESC LIMIT 1",
            [$accountId, $fromDate]
        );

        // If no prior transaction exists, start from zero
        $lastBalance = $lastTxBefore ? floatval($lastTxBefore['balance_after']) : 0;

        // Get all transactions to recalculate, ordered chronologically
        $transactionsToUpdate = $this->db->fetchAll(
            "SELECT id, type, amount FROM transactions WHERE account_id = ? AND date >= ? ORDER BY date ASC, created_at ASC",
            [$accountId, $fromDate]
        );

        // Recalculate each transaction's balance
        foreach ($transactionsToUpdate as $tx) {
            $lastBalance = $this->calculateNewBalance(['balance' => $lastBalance], $tx['type'], $tx['amount']);
            $this->db->execute("UPDATE transactions SET balance_after = ? WHERE id = ?", [$lastBalance, $tx['id']]);
        }

        // Finally, update the main account balance with the last calculated balance
        $this->updateAccountBalance($accountId, $lastBalance);
    }

    /**
     * Récupère tous les documents associés à une transaction
     *
     * @param string $transactionId L'UUID de la transaction
     * @return array Tableau des documents
     */
    public function getTransactionDocuments($transactionId)
    {
        // Vérifier d'abord quelle colonne existe (upload_date ou uploaded_at)
        $columnCheck = $this->db->fetchOne(
            "SELECT column_name 
             FROM information_schema.columns 
             WHERE table_name = 'transaction_documents' 
             AND column_name IN ('upload_date', 'uploaded_at')
             LIMIT 1"
        );
        
        $dateColumn = $columnCheck ? $columnCheck['column_name'] : 'upload_date';
        
        $sql = "SELECT id, file_name, original_name, file_path, file_size, mime_type, {$dateColumn} as upload_date 
                FROM transaction_documents 
                WHERE transaction_id = ? 
                ORDER BY {$dateColumn} DESC";
        
        return $this->db->fetchAll($sql, [$transactionId]);
    }

    /**
     * Ajoute un document à une transaction
     *
     * @param string $transactionId L'UUID de la transaction
     * @param array $documentData Les données du document (file_name, file_path, file_size, mime_type)
     * @return string L'UUID du document inséré
     */
    public function addTransactionDocument($transactionId, $documentData)
    {
        $sql = "INSERT INTO transaction_documents (id, transaction_id, file_name, original_name, file_path, file_size, mime_type)
                VALUES (uuid_generate_v4(), ?, ?, ?, ?, ?, ?)
                RETURNING id";
        
        $params = [
            $transactionId,
            $documentData['file_name'],
            $documentData['original_name'] ?? $documentData['file_name'], // Utiliser file_name si original_name n'est pas fourni
            $documentData['file_path'],
            $documentData['file_size'] ?? null,
            $documentData['mime_type'] ?? null
        ];
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['id'];
    }

    /**
     * Supprime un document
     *
     * @param string $documentId L'UUID du document
     * @return bool True en cas de succès
     */
    public function deleteTransactionDocument($documentId)
    {
        // Récupérer les informations du document avant de le supprimer
        $document = $this->db->fetchOne("SELECT file_path FROM transaction_documents WHERE id = ?", [$documentId]);
        
        if ($document) {
            // Supprimer le fichier physique si il existe
            $filePath = __DIR__ . '/../' . $document['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Supprimer l'enregistrement de la base de données
            $this->db->execute("DELETE FROM transaction_documents WHERE id = ?", [$documentId]);
            return true;
        }
        
        return false;
    }
}
