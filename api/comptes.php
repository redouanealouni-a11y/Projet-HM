<?php

require_once __DIR__ . '/../config/database.php';

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
require_once __DIR__ . '/../classes/Compte.php';

try {
    $compte = new Compte();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['PATH_INFO'] ?? '';

    // Parse input data
    $input = get_request_input();

    // GET /api/comptes - Récupérer tous les comptes
    if ($method === 'GET' && empty($path)) {
        $type = $_GET['type'] ?? null;
        $comptes = $compte->getAll($type);

        jsonResponse([
            'success' => true,
            'data' => $comptes,
            'count' => count($comptes)
        ]);
    }

    // GET /api/comptes/with-stats - Récupérer tous les comptes avec statistiques
    elseif ($method === 'GET' && $path === '/with-stats') {
        $comptes = $compte->getAllWithTransactionCount();

        jsonResponse([
            'success' => true,
            'data' => $comptes,
            'count' => count($comptes)
        ]);
    }

    // GET /api/comptes/balance-by-type - Récupérer le solde total par type
    elseif ($method === 'GET' && $path === '/balance-by-type') {
        $balances = $compte->getTotalBalanceByType();

        jsonResponse([
            'success' => true,
            'data' => $balances
        ]);
    }

    // GET /api/comptes/search - Rechercher des comptes
    elseif ($method === 'GET' && $path === '/search') {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            handleError('Paramètre de recherche manquant', 400);
        }

        $query = $_GET['q'];
        $type = $_GET['type'] ?? null;

        $results = $compte->search($query, $type);

        jsonResponse([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
    }

    // GET /api/comptes?id={id} - Récupérer un compte par ID
    elseif ($method === 'GET' && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $compteData = $compte->getById($id);

        if (!$compteData) {
            handleError('Compte non trouvé', 404);
        }

        jsonResponse(['success' => true, 'data' => $compteData]);
    }

    // GET /api/comptes/{id}/stats - Récupérer les statistiques d'un compte
    elseif ($method === 'GET' && preg_match('/^\/([^\/]+)\/stats$/', $path, $matches)) {
        $id = $matches[1];
        $stats = $compte->getStats($id);

        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    // GET /api/comptes/{id}/transactions - Récupérer l'historique des transactions
    elseif ($method === 'GET' && preg_match('/^\/([^\/]+)\/transactions$/', $path, $matches)) {
        $id = $matches[1];
        $limit = intval($_GET['limit'] ?? 50);

        $transactions = $compte->getTransactionHistory($id, $limit);

        jsonResponse([
            'success' => true,
            'data' => $transactions,
            'count' => count($transactions)
        ]);
    }

    // POST /api/comptes - Créer un nouveau compte
    elseif ($method === 'POST' && empty($path)) {
        if (empty($input)) {
            handleError('Données manquantes', 400);
        }

        $newCompte = $compte->create($input);

        jsonResponse([
            'success' => true,
            'message' => 'Compte créé avec succès',
            'data' => $newCompte
        ], 201);
    }

    // PUT /api/comptes?id={id} - Mettre à jour un compte
    elseif ($method === 'PUT' && !empty($_GET['id'])) {
        $id = $_GET['id'];

        if (empty($input)) {
            handleError('Données manquantes', 400);
        }

        $updatedCompte = $compte->update($id, $input);

        jsonResponse(['success' => true, 'message' => 'Compte mis à jour avec succès', 'data' => $updatedCompte]);
    }

    // DELETE /api/comptes?id={id} - Supprimer un compte
    elseif ($method === 'DELETE' && !empty($_GET['id'])) {
        $id = $_GET['id'];

        // Vérifier si le compte peut être supprimé
        if (!$compte->canDelete($id)) {
            handleError('Impossible de supprimer un compte qui contient des transactions', 400);
        }

        $compte->delete($id);

        jsonResponse(['success' => true, 'message' => 'Compte supprimé avec succès']);
    } else {
        handleError('Endpoint non trouvé', 404);
    }

} catch (Exception $e) {
    handleError($e->getMessage(), 500);
}
