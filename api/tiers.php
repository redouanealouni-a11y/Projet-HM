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
require_once __DIR__ . '/../classes/Tiers.php';

try {
    $tiers = new Tiers();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['PATH_INFO'] ?? '';

    // Parse input data
    $input = get_request_input();

    // GET /api/tiers - Récupérer tous les tiers
    if ($method === 'GET' && empty($path)) {
        $type = $_GET['type'] ?? null;
        $tiersData = $tiers->getAll($type);

        jsonResponse([
            'success' => true,
            'data' => $tiersData,
            'count' => count($tiersData)
        ]);
    }

    // GET /api/tiers/with-stats - Récupérer tous les tiers avec statistiques
    elseif ($method === 'GET' && $path === '/with-stats') {
        $type = $_GET['type'] ?? null;
        $tiersData = $tiers->getAllWithTransactionCount($type);

        jsonResponse([
            'success' => true,
            'data' => $tiersData,
            'count' => count($tiersData)
        ]);
    }

    // GET /api/tiers/search - Rechercher des tiers
    elseif ($method === 'GET' && $path === '/search') {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            handleError('Paramètre de recherche manquant', 400);
        }

        $query = $_GET['q'];
        $type = $_GET['type'] ?? null;

        $results = $tiers->search($query, $type);

        jsonResponse([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
    }

    // GET /api/tiers/by-code/{code} - Récupérer un tiers par code
    elseif ($method === 'GET' && preg_match('/^\/by-code\/(.+)$/', $path, $matches)) {
        $code = $matches[1];
        $tiersData = $tiers->getByCode($code);

        if (!$tiersData) {
            handleError('Tiers non trouvé', 404);
        }

        jsonResponse([
            'success' => true,
            'data' => $tiersData
        ]);
    }

    // GET /api/tiers?id={id} - Récupérer un tiers par ID
    elseif ($method === 'GET' && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $tiersData = $tiers->getById($id);

        if (!$tiersData) {
            handleError('Tiers non trouvé', 404);
        }

        jsonResponse(['success' => true, 'data' => $tiersData]);
    }

    // GET /api/tiers/{id}/stats - Récupérer les statistiques d'un tiers
    elseif ($method === 'GET' && preg_match('/^\/([^\/]+)\/stats$/', $path, $matches)) {
        $id = $matches[1];
        $stats = $tiers->getStats($id);

        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    // GET /api/tiers/{id}/transactions - Récupérer l'historique des transactions
    elseif ($method === 'GET' && preg_match('/^\/([^\/]+)\/transactions$/', $path, $matches)) {
        $id = $matches[1];
        $limit = intval($_GET['limit'] ?? 50);

        $transactions = $tiers->getTransactionHistory($id, $limit);

        jsonResponse([
            'success' => true,
            'data' => $transactions,
            'count' => count($transactions)
        ]);
    }

    // POST /api/tiers - Créer un nouveau tiers
    elseif ($method === 'POST' && empty($path)) {
        if (empty($input)) {
            handleError('Données manquantes', 400);
        }

        $newTiers = $tiers->create($input);

        jsonResponse([
            'success' => true,
            'message' => 'Tiers créé avec succès',
            'data' => $newTiers
        ], 201);
    }

    // PUT /api/tiers?id={id} - Mettre à jour un tiers
    elseif ($method === 'PUT' && !empty($_GET['id'])) {
        $id = $_GET['id'];

        if (empty($input)) {
            handleError('Données manquantes', 400);
        }

        // Check if this is a solde update
        if (isset($input['operation']) && isset($input['amount'])) {
            $tiers->updateSolde($id, $input['amount'], $input['operation']);
            jsonResponse(['success' => true, 'message' => 'Solde mis à jour avec succès']);
        } else {
            $updatedTiers = $tiers->update($id, $input);
            jsonResponse(['success' => true, 'message' => 'Tiers mis à jour avec succès', 'data' => $updatedTiers]);
        }
    }

    // DELETE /api/tiers?id={id} - Supprimer un tiers
    elseif ($method === 'DELETE' && !empty($_GET['id'])) {
        $id = $_GET['id'];

        // Vérifier si le tiers peut être supprimé
        if (!$tiers->canDelete($id)) {
            handleError('Impossible de supprimer un tiers qui a des transactions associées', 400);
        }

        $tiers->delete($id);

        jsonResponse([
            'success' => true,
            'message' => 'Tiers supprimé avec succès'
        ]);
    } else {
        handleError('Endpoint non trouvé', 404);
    }

} catch (Exception $e) {
    handleError($e->getMessage(), 500);
}
