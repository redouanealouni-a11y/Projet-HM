<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = Database::getInstance();
    
    $fournisseurs = $db->fetchAll(
        "SELECT id, raison_sociale 
         FROM tiers 
         WHERE is_active = TRUE 
           AND type = 'fournisseur' 
         ORDER BY raison_sociale ASC"
    );

    echo json_encode([
        'success' => true,
        'data' => $fournisseurs,
        'count' => count($fournisseurs)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>