<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/Transaction.php';

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $transactionHandler = new Transaction();
    $method = $_SERVER['REQUEST_METHOD'];

    // Définir le répertoire de stockage des documents
    $uploadDir = __DIR__ . '/../uploads/documents/';
    
    // Créer le répertoire s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // POST - Upload un document
    if ($method === 'POST') {
        if (!isset($_FILES['file']) || !isset($_POST['transaction_id'])) {
            throw new Exception("Éléments requis manquants");
        }

        $transactionId = $_POST['transaction_id'];
        $file = $_FILES['file'];

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale du formulaire',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
                UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'\u00e9crire le fichier sur le disque',
                UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension PHP'
            ];
            $errorMsg = $errorMessages[$file['error']] ?? "Erreur d'upload inconnue (code {$file['error']})";
            throw new Exception($errorMsg);
        }

        // Limite de taille : 10 Mo
        $maxSize = 10 * 1024 * 1024; // 10 MB en octets
        if ($file['size'] > $maxSize) {
            throw new Exception("Le fichier est trop volumineux. Taille maximale : 10 Mo");
        }

        // Vérifier que le fichier a bien été uploadé via HTTP POST
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Fichier non valide");
        }

        // Générer un nom de fichier unique et sécurisé
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Valider l'extension
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Type de fichier non autorisé. Extensions acceptées : " . implode(', ', $allowedExtensions));
        }
        
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = 'uploads/documents/' . $fileName;
        $fullPath = $uploadDir . $fileName;

        // Déplacer le fichier uploadé
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Impossible de sauvegarder le fichier. Vérifiez les permissions du répertoire.");
        }

        // Ajouter le document à la base de données
        $documentData = [
            'file_name' => $fileName,           // Nom stocké sur le serveur (généré)
            'original_name' => $file['name'],   // Nom original du fichier
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'mime_type' => $file['type']
        ];

        try {
            $documentId = $transactionHandler->addTransactionDocument($transactionId, $documentData);
        } catch (Exception $e) {
            // Si l'insertion en base échoue, supprimer le fichier
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            throw new Exception("Erreur lors de l'enregistrement en base : " . $e->getMessage());
        }

        jsonResponse([
            'success' => true,
            'message' => 'Document uploadé avec succès',
            'data' => [
                'id' => $documentId,
                'file_name' => $file['name'],
                'file_path' => $filePath,
                'file_size' => $file['size']
            ]
        ], 201);
    }

    // DELETE - Supprimer un document
    elseif ($method === 'DELETE') {
        $documentId = $_GET['id'] ?? null;
        
        if (!$documentId) {
            throw new Exception("ID du document manquant");
        }

        $result = $transactionHandler->deleteTransactionDocument($documentId);
        
        if ($result) {
            jsonResponse([
                'success' => true,
                'message' => 'Document supprimé avec succès'
            ]);
        } else {
            throw new Exception("Document non trouvé");
        }
    }

    else {
        handleError('Méthode non autorisée', 405);
    }

} catch (Exception $e) {
    error_log("Erreur API upload document: " . $e->getMessage());
    handleError($e->getMessage(), 500);
}
