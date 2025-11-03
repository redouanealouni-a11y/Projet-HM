<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Transaction.php';
require_once __DIR__ . '/../includes/helpers.php';

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Vérifier que la méthode est DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        handleError('Méthode non autorisée', 405);
    }

    // Vérifier que l'ID du document est fourni
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        handleError('ID du document manquant', 400);
    }

    $documentId = $_GET['id'];

    // Utiliser la classe Transaction pour supprimer le document
    $transactionHandler = new Transaction();
    $result = $transactionHandler->deleteTransactionDocument($documentId);

    if ($result) {
        jsonResponse([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ]);
    } else {
        handleError('Document non trouvé ou déjà supprimé', 404);
    }

} catch (Exception $e) {
    error_log("Erreur lors de la suppression du document: " . $e->getMessage());
    handleError($e->getMessage(), 500);
}
