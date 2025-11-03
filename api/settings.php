<?php

require_once __DIR__ . '/../config/database.php';

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET /api/settings - Récupérer tous les paramètres
    if ($method === 'GET') {
        $settings = $db->fetchAll(
            "SELECT setting_key, setting_value FROM settings ORDER BY setting_key"
        );

        // Convertir en format clé-valeur
        $settingsData = [];
        foreach ($settings as $setting) {
            $settingsData[$setting['setting_key']] = $setting['setting_value'];
        }

        jsonResponse([
            'success' => true,
            'data' => $settingsData
        ]);
    } else {
        handleError('Endpoint non trouvé', 404);
    }

} catch (Exception $e) {
    error_log("Erreur API settings: " . $e->getMessage());
    handleError($e->getMessage(), 500);
}
