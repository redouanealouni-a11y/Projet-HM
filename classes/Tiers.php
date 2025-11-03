<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Manages third-parties (Tiers), such as clients and suppliers.
 * Provides methods for creating, retrieving, updating, and deleting third-parties,
 * as well as fetching related statistics and transaction history.
 */
class Tiers
{
    /**
     * @var Database The database connection instance.
     */
    private $db;

    /**
     * Tiers constructor.
     * Initializes the database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new third-party.
     *
     * @param array $data The data for the new third-party.
     * @return mixed The newly created third-party object.
     * @throws Exception if required fields are missing or data is invalid.
     */
public function create($data)
{
    $errors = validateRequired($data, ['type', 'raison_sociale']);
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    if (!in_array($data['type'], ['client', 'fournisseur'])) {
        throw new Exception("Type de tiers invalide. Doit être 'client' ou 'fournisseur'");
    }

    if (!empty($data['email']) && ($emailError = validateEmail($data['email']))) {
        throw new Exception($emailError);
    }

    if (empty($data['code'])) {
        $data['code'] = $this->generateCode($data['type'], $data['raison_sociale']);
    }

    try {
        $sql = "
            INSERT INTO tiers (
                id, code, type, type_tiers, raison_sociale, contact, telephone, email, siret, adresse, notes, solde,
                reference, famille_code, statut, note_interne,
                code_postal, ville, wilaya, adresse_livraison,
                telephone_fixe, mobile, fax, site_web,
                identifiant_fiscal, nis, registre_commerce, article_imposition,
                code_comptable, numero_compte, rib, exoneration_tva,
                mode_paiement, conditions_echeance,
                date_creation, date_modification, date1, date2, date3,
                mots_cles, solvabilite, remarques, commercial_id
            )
            VALUES (
                uuid_generate_v4(),
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            RETURNING *";

        $nullIfEmpty = fn($v) => ($v === '' || $v === null) ? null : $v;

        // ✅ Conversion robuste du champ exoneration_tva
     $exonerationTVA = false; // valeur par défaut

$exonerationTVA = false; // valeur par défaut

if (!empty($data['exoneration_tva'])) {
    $val = strtolower(trim((string)$data['exoneration_tva']));
    $exonerationTVA = in_array($val, ['true', '1', 'oui', 'on'], true);
}

// ✅ IMPORTANT : Forcer un vrai booléen
// Aucune autre valeur ne peut passer → reste false
        $params = [
            $data['code'],
            $data['type'],
            $data['type_tiers'] ?? 'particulier',
            $data['raison_sociale'],
            $data['contact'] ?? '',
            $data['telephone'] ?? '',
            $data['email'] ?? '',
            $data['siret'] ?? '',
            $data['adresse'] ?? '',
            $data['notes'] ?? '',
            $data['solde'] ?? 0,
            $data['reference'] ?? '',
            $data['famille_code'] ?? '',
            $data['statut'] ?? 'actif',
            $data['note_interne'] ?? '',
            $data['code_postal'] ?? '',
            $data['ville'] ?? '',
            $data['wilaya'] ?? '',
            $data['adresse_livraison'] ?? '',
            $data['telephone_fixe'] ?? '',
            $data['mobile'] ?? '',
            $data['fax'] ?? '',
            $data['site_web'] ?? '',
            $data['identifiant_fiscal'] ?? '',
            $data['nis'] ?? '',
            $data['registre_commerce'] ?? '',
            $data['article_imposition'] ?? '',
            $data['code_comptable'] ?? '',
            $data['numero_compte'] ?? '',
            $data['rib'] ?? '',

            $exonerationTVA,
            $data['mode_paiement'] ?? '',
            $data['conditions_echeance'] ?? '',
            $nullIfEmpty($data['date_creation'] ?? date('Y-m-d H:i:s')),
            $nullIfEmpty($data['date_modification'] ?? date('Y-m-d H:i:s')),
            $nullIfEmpty($data['date1'] ?? null),
            $nullIfEmpty($data['date2'] ?? null),
            $nullIfEmpty($data['date3'] ?? null),
            $data['mots_cles'] ?? '',
            $data['solvabilite'] ?? '',
            $data['remarques'] ?? '',
            $data['commercial_id'] ?? null
        ];

        return $this->db->fetchOne($sql, $params);

    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'tiers_code_key') !== false) {
            throw new Exception("Un tiers avec ce code existe déjà");
        }
        throw $e;
    }
}



    /**
     * Updates an existing third-party.
     *
     * @param string $id The UUID of the third-party to update.
     * @param array $data The new data for the third-party.
     * @return mixed The updated third-party object.
     * @throws Exception if the third-party is not found or data is invalid.
     */
    public function update($id, $data)
    {
        if (!$this->getById($id)) {
            throw new Exception("Tiers non trouvé");
        }

        if (!empty($data['email']) && ($emailError = validateEmail($data['email']))) {
            throw new Exception($emailError);
        }

        $updates = [];
        $params = [];
        $fields = ['code', 'raison_sociale', 'contact', 'telephone', 'email', 'siret', 'adresse', 'notes'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (isset($data['solde']) && is_numeric($data['solde'])) {
            $updates[] = "solde = ?";
            $params[] = $data['solde'];
        }

        if (empty($updates)) {
            return $this->getById($id);
        }

        $params[] = $id;
        $sql = "UPDATE tiers SET " . implode(', ', $updates) . " WHERE id = ? RETURNING *";

        try {
            return $this->db->fetchOne($sql, $params);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'tiers_code_key') !== false) {
                throw new Exception("Un tiers avec ce code existe déjà");
            }
            throw $e;
        }
    }

    /**
     * Retrieves all active third-parties, optionally filtered by type.
     *
     * @param string|null $type The type to filter by ('client' or 'fournisseur').
     * @return array An array of third-party objects.
     */
    public function getAll($type = null)
    {
        $sql = "
            SELECT t.*, 
                   COALESCE(recettes.total_recettes, 0) - COALESCE(depenses.total_depenses, 0) as solde_calcule,
                   COALESCE(recettes.total_recettes, 0) as total_recettes,
                   COALESCE(depenses.total_depenses, 0) as total_depenses,
                   COALESCE(trans_count.transaction_count, 0) as transaction_count
            FROM tiers t
            LEFT JOIN (
                SELECT tiers_id, SUM(amount) as total_recettes
                FROM transactions 
                WHERE type = 'recette' AND tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) recettes ON t.id = recettes.tiers_id
            LEFT JOIN (
                SELECT tiers_id, SUM(amount) as total_depenses
                FROM transactions 
                WHERE type = 'depense' AND tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) depenses ON t.id = depenses.tiers_id
            LEFT JOIN (
                SELECT tiers_id, COUNT(*) as transaction_count
                FROM transactions 
                WHERE tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) trans_count ON t.id = trans_count.tiers_id
            WHERE t.is_active = TRUE";
        
        $params = [];
        if ($type) {
            $sql .= " AND t.type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY t.raison_sociale ASC";
        
        $result = $this->db->fetchAll($sql, $params);
        
        // Remplacer le solde par le solde calculé
        foreach ($result as &$tiers) {
            $tiers['solde'] = $tiers['solde_calcule'];
            
            // Ajouter des informations utiles pour le debug
            $tiers['solde_debug'] = [
                'recettes' => floatval($tiers['total_recettes']),
                'depenses' => floatval($tiers['total_depenses']),
                'solde_calcule' => floatval($tiers['solde_calcule']),
                'transaction_count' => intval($tiers['transaction_count'])
            ];
        }
        
        return $result;
    }

    /**
     * Retrieves a single active third-party by its UUID.
     *
     * @param string $id The UUID of the third-party.
     * @return mixed The third-party object if found, otherwise false.
     */
    public function getById($id)
    {
        $sql = "
            SELECT t.*, 
                   COALESCE(recettes.total_recettes, 0) - COALESCE(depenses.total_depenses, 0) as solde_calcule,
                   COALESCE(recettes.total_recettes, 0) as total_recettes,
                   COALESCE(depenses.total_depenses, 0) as total_depenses,
                   COALESCE(trans_count.transaction_count, 0) as transaction_count
            FROM tiers t
            LEFT JOIN (
                SELECT tiers_id, SUM(amount) as total_recettes
                FROM transactions 
                WHERE type = 'recette' AND tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) recettes ON t.id = recettes.tiers_id
            LEFT JOIN (
                SELECT tiers_id, SUM(amount) as total_depenses
                FROM transactions 
                WHERE type = 'depense' AND tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) depenses ON t.id = depenses.tiers_id
            LEFT JOIN (
                SELECT tiers_id, COUNT(*) as transaction_count
                FROM transactions 
                WHERE tiers_id IS NOT NULL
                GROUP BY tiers_id
            ) trans_count ON t.id = trans_count.tiers_id
            WHERE t.id = ? AND t.is_active = TRUE";
        
        $result = $this->db->fetchOne($sql, [$id]);
        
        if ($result) {
            // Remplacer le solde par le solde calculé
            $result['solde'] = $result['solde_calcule'];
            
            // Ajouter des informations utiles pour le debug
            $result['solde_debug'] = [
                'recettes' => floatval($result['total_recettes']),
                'depenses' => floatval($result['total_depenses']),
                'solde_calcule' => floatval($result['solde_calcule']),
                'transaction_count' => intval($result['transaction_count'])
            ];
        }
        
        return $result;
    }

    /**
     * Retrieves a single active third-party by its code.
     *
     * @param string $code The code of the third-party.
     * @return mixed The third-party object if found, otherwise false.
     */
    public function getByCode($code)
    {
        return $this->db->fetchOne("SELECT * FROM tiers WHERE code = ? AND is_active = TRUE", [$code]);
    }

    /**
     * Deletes a third-party (soft delete).
     *
     * @param string $id The UUID of the third-party to delete.
     * @return int The number of affected rows.
     * @throws Exception if the third-party has associated transactions.
     */
    public function delete($id)
    {
        if (!$this->canDelete($id)) {
            throw new Exception("Impossible de supprimer un tiers qui a des transactions associées");
        }
        return $this->db->execute("UPDATE tiers SET is_active = FALSE WHERE id = ?", [$id]);
    }

    /**
     * Searches for active third-parties by a query string.
     *
     * @param string $query The search query.
     * @param string|null $type The type to filter by.
     * @return array An array of matching third-party objects.
     */
    public function search($query, $type = null)
    {
        $sql = "SELECT * FROM tiers WHERE is_active = TRUE AND (raison_sociale ILIKE ? OR code ILIKE ?)";
        $params = ['%' . $query . '%', '%' . $query . '%'];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY raison_sociale ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Retrieves the transaction history for a specific third-party.
     *
     * @param string $id The UUID of the third-party.
     * @param int $limit The maximum number of transactions to retrieve.
     * @return array An array of transaction objects.
     */
    public function getTransactionHistory($id, $limit = 50)
    {
        return $this->db->fetchAll("SELECT * FROM v_transactions_details WHERE tiers_id = ? ORDER BY date DESC, created_at DESC LIMIT ?", [$id, $limit]);
    }

    /**
     * Updates the balance for a specific third-party.
     *
     * @param string $id The UUID of the third-party.
     * @param float $amount The amount to add or subtract.
     * @param string $operation The operation ('add' or 'subtract').
     * @return int The number of affected rows.
     * @throws Exception if the third-party is not found or data is invalid.
     */
    public function updateSolde($id, $amount, $operation = 'add')
    {
        $tiers = $this->getById($id);
        if (!$tiers) {
            throw new Exception("Tiers non trouvé");
        }
        if (!is_numeric($amount)) {
            throw new Exception("Le montant doit être numérique");
        }
        $newSolde = ($operation === 'add') ? $tiers['solde'] + $amount : $tiers['solde'] - $amount;
        return $this->db->execute("UPDATE tiers SET solde = ? WHERE id = ?", [$newSolde, $id]);
    }

    /**
     * Gets statistics for a specific third-party.
     *
     * @param string $id The UUID of the third-party.
     * @return array An array containing third-party data and transaction stats.
     * @throws Exception if the third-party is not found.
     */
    public function getStats($id)
    {
        $tiers = $this->getById($id);
        if (!$tiers) {
            throw new Exception("Tiers non trouvé");
        }
        $stats = $this->db->fetchOne("
            SELECT COUNT(*) as total_transactions,
                   COALESCE(SUM(CASE WHEN type = 'recette' THEN amount ELSE 0 END), 0) as total_recettes,
                   COALESCE(SUM(CASE WHEN type = 'depense' THEN amount ELSE 0 END), 0) as total_depenses,
                   MAX(date) as derniere_transaction
            FROM transactions WHERE tiers_id = ?", [$id]);
        return array_merge($tiers, $stats);
    }

    /**
     * Retrieves all active third-parties with a count of their transactions.
     *
     * @param string|null $type The type to filter by.
     * @return array An array of third-party objects with transaction counts.
     */
    public function getAllWithTransactionCount($type = null)
    {
        $sql = "
            SELECT t.*, COALESCE(tr.transaction_count, 0) as transaction_count
            FROM tiers t
            LEFT JOIN (
                SELECT tiers_id, COUNT(*) as transaction_count
                FROM transactions WHERE tiers_id IS NOT NULL GROUP BY tiers_id
            ) tr ON t.id = tr.tiers_id
            WHERE t.is_active = TRUE";
        $params = [];
        if ($type) {
            $sql .= " AND t.type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY t.raison_sociale ASC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Checks if a third-party can be safely deleted.
     *
     * @param string $id The UUID of the third-party.
     * @return bool True if the third-party can be deleted, false otherwise.
     */
    public function canDelete($id)
    {
        $count = $this->db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE tiers_id = ?", [$id]);
        return $count['count'] == 0;
    }

    /**
     * Generates a unique code for a new third-party.
     *
     * @param string $type The type of the third-party ('client' or 'fournisseur').
     * @param string $raisonSociale The name of the third-party.
     * @return string The generated unique code.
     */
    private function generateCode($type, $raisonSociale)
    {
        $prefix = ($type === 'client') ? 'CLI' : 'FOU';
        $raisonPart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $raisonSociale), 0, 3));
        $baseCode = $prefix . $raisonPart;

        $sql = "SELECT code FROM tiers WHERE code LIKE ? ORDER BY code DESC LIMIT 1";
        $lastCode = $this->db->fetchOne($sql, [$baseCode . '%']);

        $nextNumber = 1;
        if ($lastCode) {
            $lastNumber = intval(substr($lastCode['code'], strlen($baseCode)));
            $nextNumber = $lastNumber + 1;
        }

        return $baseCode . sprintf('%03d', $nextNumber);
    }
}
