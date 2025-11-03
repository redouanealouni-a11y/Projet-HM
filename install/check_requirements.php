<?php

/**
 * Script de vérification des prérequis
 * Application de Gestion de Caisse PHP/PostgreSQL
 */

echo "================================================================\n";
echo "    VERIFICATION DES PREREQUIS - CAISSE REGIE PHP/POSTGRESQL\n";
echo "================================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Vérification de la version PHP
echo "1. Vérification de PHP...\n";
echo "-------------------------\n";

$phpVersion = PHP_VERSION;
$minPhpVersion = '7.4.0';

if (version_compare($phpVersion, $minPhpVersion, '>=')) {
    $success[] = "PHP $phpVersion détecté (minimum requis: $minPhpVersion)";
} else {
    $errors[] = "PHP $phpVersion détecté. Version minimum requise: $minPhpVersion";
}

// 2. Vérification des extensions PHP
echo "\n2. Vérification des extensions PHP...\n";
echo "-------------------------------------\n";

$requiredExtensions = [
    'pdo' => 'PDO (PHP Data Objects)',
    'pdo_pgsql' => 'PDO PostgreSQL',
    'pgsql' => 'PostgreSQL',
    'json' => 'JSON',
    'mbstring' => 'Multibyte String',
    'openssl' => 'OpenSSL'
];

foreach ($requiredExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        $success[] = "Extension $description: OK";
    } else {
        $errors[] = "Extension manquante: $description ($ext)";
    }
}

$recommendedExtensions = [
    'curl' => 'cURL',
    'gd' => 'GD Graphics',
    'zip' => 'ZIP'
];

foreach ($recommendedExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        $success[] = "Extension recommandée $description: OK";
    } else {
        $warnings[] = "Extension recommandée manquante: $description ($ext)";
    }
}

// 3. Vérification de la configuration PHP
echo "\n3. Vérification de la configuration PHP...\n";
echo "-------------------------------------------\n";

// Memory limit
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$minMemory = return_bytes('64M');

if ($memoryLimitBytes >= $minMemory || $memoryLimit == -1) {
    $success[] = "Limite mémoire: $memoryLimit (minimum recommandé: 64M)";
} else {
    $warnings[] = "Limite mémoire faible: $memoryLimit (recommandé: 64M ou plus)";
}

// File uploads
if (ini_get('file_uploads')) {
    $success[] = "Upload de fichiers: Activé";
    $maxFileSize = ini_get('upload_max_filesize');
    $success[] = "Taille max upload: $maxFileSize";
} else {
    $warnings[] = "Upload de fichiers: Désactivé";
}

// Timezone
$timezone = date_default_timezone_get();
$success[] = "Timezone par défaut: $timezone";

// 4. Test de connexion PostgreSQL
echo "\n4. Test de connexion PostgreSQL...\n";
echo "-----------------------------------\n";

$testConnection = testPostgreSQLConnection();

if ($testConnection['success']) {
    $success[] = "Connexion PostgreSQL: " . $testConnection['message'];
} else {
    $errors[] = "Connexion PostgreSQL: " . $testConnection['message'];
}

// 5. Vérification des permissions de fichiers
echo "\n5. Vérification des permissions...\n";
echo "-----------------------------------\n";

$directories = [
    'config' => 'Configuration',
    'logs' => 'Logs (sera créé si nécessaire)'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $success[] = "Dossier $description: Accessible en écriture";
        } else {
            $warnings[] = "Dossier $description: Non accessible en écriture";
        }
    } else {
        if (mkdir($dir, 0755, true)) {
            $success[] = "Dossier $description: Créé avec succès";
        } else {
            $errors[] = "Impossible de créer le dossier $description";
        }
    }
}

// 6. Affichage du résumé
echo "\n================================================================\n";
echo "                        RESUME\n";
echo "================================================================\n";

if (!empty($success)) {
    echo "\n✓ SUCCES (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  ✓ $msg\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠ AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  ⚠ $msg\n";
    }
}

if (!empty($errors)) {
    echo "\n✗ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  ✗ $msg\n";
    }
}

echo "\n================================================================\n";

if (empty($errors)) {
    echo "✓ TOUS LES PREREQUIS SONT SATISFAITS !\n";
    echo "Vous pouvez procéder à l'installation.\n";
    exit(0);
} else {
    echo "✗ PREREQUIS NON SATISFAITS !\n";
    echo "Veuillez corriger les erreurs avant de continuer.\n";
    exit(1);
}

/**
 * Convertit une valeur de taille en octets
 */
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = intval($val);

    switch ($last) {
        case 'g':
            $val *= 1024;
            // no break
        case 'm':
            $val *= 1024;
            // no break
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/**
 * Test de connexion PostgreSQL
 */
function testPostgreSQLConnection()
{
    // Utiliser la configuration de l'application si elle existe
    if (file_exists(__DIR__ . '/../config/config_local.php')) {
        require_once __DIR__ . '/../config/config_local.php';
        $testParams = [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASS
        ];
    } else {
        // Paramètres de test par défaut
        $testParams = [
            'host' => 'localhost',
            'port' => '5432',
            'dbname' => 'caisse_regie',
            'user' => 'caisse_user',
            'password' => 'caisse_password_123'
        ];
    }

    try {
        // Tenter une connexion basique
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            $testParams['host'],
            $testParams['port'],
            $testParams['dbname']
        );

        $pdo = new PDO($dsn, $testParams['user'], $testParams['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);

        // Test simple
        $stmt = $pdo->query('SELECT version()');
        $version = $stmt->fetchColumn();

        return [
            'success' => true,
            'message' => "OK - PostgreSQL accessible ($version)"
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Erreur - " . $e->getMessage()
        ];
    }
}
