<?php

// ✅ AJOUT : Bufferiser la sortie pour nettoyer tout HTML inattendu
ob_start();

// ✅ AJOUT : Supprimer les erreurs affichées (évite HTML dans JSON)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ✅ AJOUT : Définir ALLOWED_ORIGIN si non défini (pour éviter notice "Undefined constant ALLOWED_ORIGIN")
if (!defined('ALLOWED_ORIGIN')) {
    define('ALLOWED_ORIGIN', '*'); // Autorise tout (pour dev), ou remplace par 'http://localhost:8080' pour sécurité
}

require_once __DIR__ . '/../config/database.php';

// ✅ AJOUT : Fonction utilitaire pour parser l'input (manquante)
function get_request_input() {
    $input = null;
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $input = json_decode($rawInput, true);
        }
    }
    
    // Fallback sur $_POST si JSON invalide ou vide
    if (empty($input)) {
        $input = $_POST;
    }
    
    return is_array($input) ? $input : [];
}

// ✅ AJOUT : Fonctions utilitaires pour handleError et jsonResponse (si non définies ailleurs)
if (!function_exists('handleError')) {
    function handleError($message, $statusCode = 400) {
        if (ob_get_level()) ob_clean(); // Nettoie buffer
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8'); // Force JSON
        echo json_encode([
            'success' => false,
            'error' => true,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($data) {
        if (ob_get_level()) ob_clean(); // Nettoie buffer
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Gérer les différentes actions via query string ou input
    $action = $_GET['action'] ?? null;
    
    // Debug: log des paramètres reçus
    error_log("🔍 API catégories: method={$method}, action=" . ($action ?? 'null') . ", id=" . ($_GET['id'] ?? 'non défini'));
    
    // Debug: log des conditions
    $generalCondition = ($method === 'GET' && ($action === 'list' || (!$action && !isset($_GET['id']))));
    error_log("🔍 Condition générale GET: " . ($generalCondition ? 'TRUE' : 'FALSE'));
    
    if ($method === 'GET') {
        $specificCondition = isset($_GET['id']) && $_GET['id'] !== '' && $_GET['id'] === 'undefined' && $_GET['id'] !== 'null';
        error_log("🔍 Condition spécifique GET avec ID valide: " . ($specificCondition ? 'TRUE' : 'FALSE'));
    }
    if (!$action) {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;
    }

    // Parse input data
    $input = get_request_input();

    // GET /api/categories - Récupérer toutes les catégories (actives et inactives)
    if ($method === 'GET' && ($action === 'list' || (!$action && !isset($_GET['id'])))) {
        $includeInactive = $_GET['include_inactive'] ?? false;
        
        $whereClause = $includeInactive ? "1=1" : "actif = true";
        $categories = $db->fetchAll(
            "SELECT 
                id, 
                code, 
                nom, 
                description, 
                icone, 
                couleur, 
                actif, 
                ordre_affichage,
                created_at,
                updated_at
            FROM categories_pieces_achat 
            WHERE {$whereClause}
            ORDER BY ordre_affichage ASC, nom ASC"
        );

        jsonResponse([
            'success' => true,
            'data' => $categories,
            'count' => count($categories)
        ]);
    }

    // GET /api/categories?id={id} - Récupérer une catégorie par ID
    elseif ($method === 'GET') {
        // ✅ CORRECTION: Validation complète à l'entrée
        if (!isset($_GET['id']) || $_GET['id'] === '' || $_GET['id'] === 'undefined' || $_GET['id'] === 'null') {
            handleError('ID de la catégorie requis et invalide', 400);
        }
        
        // Convertir en entier et vérifier
        $idRaw = $_GET['id'];  // ✅ CORRECTION: Définir $idRaw pour éviter la notice PHP
        $id = intval($idRaw);
        if ($id <= 0) {
            handleError('ID de catégorie doit être un nombre positif', 400);
        }
        
        error_log("🔍 Récupération catégorie avec ID: {$id} (raw: {$idRaw})");
        
        $category = $db->fetchOne(
            "SELECT 
                id, 
                code, 
                nom, 
                description, 
                icone, 
                couleur, 
                actif, 
                ordre_affichage,
                created_at,
                updated_at
            FROM categories_pieces_achat 
            WHERE id = ?",
            [$id]
        );

        if (!$category) {
            handleError('Catégorie non trouvée avec ID: ' . $id, 404);
        }

        jsonResponse(['success' => true, 'data' => $category]);
    }

    // POST /api/categories - Créer une nouvelle catégorie
    elseif ($method === 'POST' && (!$action || $action === 'create')) {
        $data = $input;
        
        // Validation des données
        if (empty($data['code']) || empty($data['nom'])) {
            handleError('Le code et le nom sont obligatoires', 400);
        }
        
        // Vérifier que le code n'existe pas déjà
        $existing = $db->fetchOne(
            "SELECT id FROM categories_pieces_achat WHERE code = ?",
            [strtoupper(trim($data['code']))]
        );
        
        if ($existing) {
            handleError('Ce code existe déjà', 409);
        }
        
        // Préparer les données
        $categoryData = [
            'code' => strtoupper(trim($data['code'])),
            'nom' => trim($data['nom']),
            'description' => trim($data['description'] ?? ''),
            'icone' => $data['icone'] ?? 'fas fa-tag',
            'couleur' => $data['couleur'] ?? '#3B82F6',
            'actif' => $data['actif'] ?? true,
            'ordre_affichage' => intval($data['ordre_affichage'] ?? 0)
        ];
        
        // Préparer l'insertion avec toutes les colonnes
        $categoryData['created_at'] = date('Y-m-d H:i:s');
        $categoryData['updated_at'] = date('Y-m-d H:i:s');
        
        // Construire la requête INSERT avec RETURNING id
        $columns = array_keys($categoryData);
        $placeholders = str_repeat('?,', count($categoryData) - 1) . '?';
        $sql = "INSERT INTO categories_pieces_achat (" . implode(', ', $columns) . ") VALUES ($placeholders) RETURNING id";
        
        // Exécuter l'insertion
        $stmt = $db->query($sql, array_values($categoryData));
        $categoryId = $stmt->fetchColumn();
        
        if (!$categoryId) {
            handleError('Erreur lors de la création de la catégorie', 500);
        }
        
        // Récupérer la catégorie créée
        $category = $db->fetchOne(
            "SELECT * FROM categories_pieces_achat WHERE id = ?",
            [$categoryId]
        );
        
        jsonResponse([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $category
        ]);
    }

    // PUT /api/categories - Modifier une catégorie
    elseif ($method === 'PUT' || ($method === 'POST' && $action === 'update')) {
        $data = $input;
        
        // ✅ CORRECTION: Récupérer et valider l'ID depuis l'URL ou depuis le body
        $categoryIdRaw = $_GET['id'] ?? ($data['id'] ?? null);
        
        if (!$categoryIdRaw || $categoryIdRaw === 'undefined' || $categoryIdRaw === 'null' || $categoryIdRaw === '') {
            handleError('ID de la catégorie requis et invalide', 400);
        }
        
        // Convertir en entier
        $categoryId = intval($categoryIdRaw);
        
        // Vérifier que la conversion a réussi
        if ($categoryId <= 0) {
            handleError('ID de catégorie doit être un nombre positif', 400);
        }
        
        error_log("✏️ Modification catégorie avec ID: {$categoryId} (raw: {$categoryIdRaw})");
        
        // Vérifier que la catégorie existe
        $existing = $db->fetchOne(
            "SELECT id FROM categories_pieces_achat WHERE id = ?",
            [$categoryId]
        );
        
        if (!$existing) {
            handleError('Catégorie non trouvée', 404);
        }
        
        // Vérifier l'unicité du code (sauf pour la catégorie actuelle)
        if (!empty($data['code'])) {
            $codeExists = $db->fetchOne(
                "SELECT id FROM categories_pieces_achat WHERE code = ? AND id != ?",
                [strtoupper(trim($data['code'])), $categoryId]
            );
            
            if ($codeExists) {
                handleError('Ce code existe déjà', 409);
            }
        }
        
        // Préparer les données de mise à jour
        $updateData = [];
        $allowedFields = ['code', 'nom', 'description', 'icone', 'couleur', 'actif', 'ordre_affichage'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'code') {
                    $updateData[$field] = strtoupper(trim($data[$field]));
                } elseif ($field === 'ordre_affichage') {
                    $updateData[$field] = intval($data[$field]);
                } elseif ($field === 'actif') {
                    $updateData[$field] = (bool)$data[$field];
                } else {
                    $updateData[$field] = trim($data[$field]);
                }
            }
        }
        
        if (empty($updateData)) {
            handleError('Aucune donnée à mettre à jour', 400);
        }
        
        // Ajouter updated_at
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Construire la requête UPDATE
        $setParts = [];
        $params = [];
        foreach ($updateData as $column => $value) {
            $setParts[] = "$column = ?";
            $params[] = $value;
        }
        $params[] = $categoryId; // Pour la clause WHERE id = ?
        
        $sql = "UPDATE categories_pieces_achat SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        // Effectuer la mise à jour
        $stmt = $db->execute($sql, $params);
        $rowCount = $stmt->rowCount();
        
        if ($rowCount === 0) {
            handleError('Erreur lors de la mise à jour', 500);
        }
        
        // Récupérer la catégorie mise à jour
        $category = $db->fetchOne(
            "SELECT * FROM categories_pieces_achat WHERE id = ?",
            [$categoryId]
        );
        
        jsonResponse([
            'success' => true,
            'message' => 'Catégorie modifiée avec succès',
            'data' => $category
        ]);
    }

    // POST /api/categories - Basculer le statut actif/inactif
    elseif ($method === 'POST' && $action === 'toggle_status') {
        $data = $input;
        
        // ✅ CORRECTION: Validation robuste de l'ID (détecte 'undefined' et 'null')
        if (!isset($data['id']) || $data['id'] === '' || $data['id'] === 'undefined' || $data['id'] === 'null') {
            handleError('ID de la catégorie requis et invalide', 400);
        }
        
        $categoryId = intval($data['id']);
        if ($categoryId <= 0) {
            handleError('ID de catégorie doit être un nombre positif', 400);
        }
        
        // Récupérer le statut actuel
        $category = $db->fetchOne(
            "SELECT actif FROM categories_pieces_achat WHERE id = ?",
            [$categoryId]
        );
        
        if (!$category) {
            handleError('Catégorie non trouvée', 404);
        }
        
        // Basculer le statut
        $newStatus = !$category['actif'];
        
        $updated = $db->update(
            'categories_pieces_achat',
            ['actif' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$categoryId]
        );
        
        if (!$updated) {
            handleError('Erreur lors du changement de statut', 500);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Statut modifié avec succès',
            'data' => ['id' => $categoryId, 'actif' => $newStatus]
        ]);
    }

    // DELETE /api/categories - Supprimer une catégorie (soft delete)
    elseif ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
        $data = $input;
        
        // ✅ CORRECTION: Validation robuste de l'ID (détecte 'undefined' et 'null')
        if (!isset($data['id']) || $data['id'] === '' || $data['id'] === 'undefined' || $data['id'] === 'null') {
            handleError('ID de la catégorie requis et invalide', 400);
        }
        
        $categoryId = intval($data['id']);
        if ($categoryId <= 0) {
            handleError('ID de catégorie doit être un nombre positif', 400);
        }
        
        // Soft delete en désactivant la catégorie
        $sql = 'UPDATE categories_pieces_achat SET actif = ?, updated_at = ? WHERE id = ?';
        $params = [false, date('Y-m-d H:i:s'), $categoryId];
        
        $updated = $db->execute($sql, $params);
        
        if ($updated === 0) {
            handleError('Catégorie non trouvée ou déjà supprimée', 404);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }

    else {
        handleError('Action non supportée', 405);
    }

} catch (Exception $e) {
    error_log('Erreur API Catégories: ' . $e->getMessage());
    handleError('Erreur serveur interne: ' . $e->getMessage(), 500);
}