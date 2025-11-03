<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Vérifier que l'ID du document est fourni
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        handleError('ID du document manquant', 400);
    }

    $documentId = $_GET['id'];

    // Récupérer les informations du document
    $db = Database::getInstance();
    $document = $db->fetchOne(
        "SELECT file_name, original_name, file_path, mime_type FROM transaction_documents WHERE id = ?",
        [$documentId]
    );

    if (!$document) {
        handleError('Document non trouvé', 404);
    }

    // Construire le chemin complet du fichier
    $filePath = __DIR__ . '/../' . $document['file_path'];

    // Vérifier que le fichier existe
    if (!file_exists($filePath)) {
        handleError('Fichier non trouvé sur le serveur', 404);
    }

    // Déterminer le nom du fichier à télécharger (utiliser original_name si disponible)
    $downloadName = $document['original_name'] ?: $document['file_name'];

    // Déterminer le type MIME
    $mimeType = $document['mime_type'] ?: 'application/octet-stream';

    // Déterminer le mode (download ou preview)
    $mode = $_GET['mode'] ?? 'download';

    // Déterminer si on doit forcer le téléchargement ou afficher inline
    if ($mode === 'preview') {
        $contentDisposition = 'inline';
    } else {
        $contentDisposition = 'attachment';
    }

    // Envoyer les en-têtes pour le téléchargement
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: ' . $contentDisposition . '; filename="' . basename($downloadName) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');

    // Lire et envoyer le fichier
    readfile($filePath);
    exit;

} catch (Exception $e) {
    error_log("Erreur lors du téléchargement du document: " . $e->getMessage());
    handleError($e->getMessage(), 500);
}
