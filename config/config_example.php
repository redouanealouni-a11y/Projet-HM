<?php

/**
 * Fichier de configuration exemple
 * Application de Gestion de Caisse PHP/PostgreSQL
 *
 * Copiez ce fichier en config_local.php et modifiez les paramètres selon votre environnement
 */

// ================================================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ================================================================

// Adresse du serveur PostgreSQL
define('DB_HOST', 'localhost');

// Port PostgreSQL (généralement 5432)
define('DB_PORT', '5432');

// Nom de la base de données
define('DB_NAME', 'caisse_regie');

// Nom d'utilisateur PostgreSQL
define('DB_USER', 'caisse_user');

// Mot de passe PostgreSQL
define('DB_PASS', 'votre_mot_de_passe_ici');

// ================================================================
// CONFIGURATION DE L'APPLICATION
// ================================================================

// Nom de l'application
define('APP_NAME', 'Gestion de Caisse Régie');

// Version de l'application
define('APP_VERSION', '2.0.0');

// Mode debug (true en développement, false en production)
define('APP_DEBUG', true);

// ================================================================
// CONFIGURATION AVANCÉE
// ================================================================

// Timezone par défaut
date_default_timezone_set('Europe/Paris');

// Charset pour PostgreSQL
define('DB_CHARSET', 'utf8');

// Timeout de connexion (en secondes)
define('DB_TIMEOUT', 30);

// ================================================================
// CONFIGURATION DES SESSIONS
// ================================================================

// Configuration des sessions uniquement en contexte web
if (php_sapi_name() !== 'cli') {
    // Durée de vie des sessions (en secondes) - 24 heures par défaut
    ini_set('session.cookie_lifetime', 86400);

    // Nom du cookie de session
    ini_set('session.name', 'CAISSE_REGIE_SESSION');

    // Sécurité des cookies
    ini_set('session.cookie_secure', 0);  // Mettre à 1 en HTTPS
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}

// ================================================================
// CONFIGURATION DES ERREURS
// ================================================================

if (APP_DEBUG) {
    // Mode développement : afficher toutes les erreurs
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    // Mode production : masquer les erreurs
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// ================================================================
// CONFIGURATION RÉGIONALE
// ================================================================

// Locale pour les formats de date et nombre
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'french');
setlocale(LC_MONETARY, 'fr_FR.UTF-8', 'fr_FR', 'french');

// ================================================================
// PARAMÈTRES DE SÉCURITÉ
// ================================================================

// Clé secrète pour le chiffrement (CHANGEZ CETTE VALEUR !)
define('APP_SECRET_KEY', 'changez_cette_cle_secrete_' . md5(__DIR__));

// Origine autorisée pour les requêtes CORS (à changer en production)
define('ALLOWED_ORIGIN', '*');

// Durée maximum d'exécution des scripts (en secondes)
set_time_limit(120);

// Limite mémoire
ini_set('memory_limit', '256M');

// ================================================================
// CONFIGURATION DES UPLOADS
// ================================================================

// Taille maximum des uploads (en bytes)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

// Nombre maximum de fichiers uploadés simultanément
ini_set('max_file_uploads', 10);

// ================================================================
// URLS ET CHEMINS
// ================================================================

// URL de base de l'application (si hébergée dans un sous-dossier)
define('APP_BASE_URL', '/');

// Dossier de logs
define('LOG_DIR', __DIR__ . '/../logs');

// Dossier de sauvegarde
define('BACKUP_DIR', __DIR__ . '/../backups');

// ================================================================
// CONFIGURATION EMAIL (pour les notifications futures)
// ================================================================

// Serveur SMTP (optionnel)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls'); // tls ou ssl

// Email expéditeur par défaut
define('FROM_EMAIL', 'noreply@votre-domaine.com');
define('FROM_NAME', 'Gestion de Caisse');

// ================================================================
// PERSONNALISATION DE L'INTERFACE
// ================================================================

// Thème par défaut
define('DEFAULT_THEME', 'default');

// Langue par défaut
define('DEFAULT_LANGUAGE', 'fr');

// Devise par défaut
define('DEFAULT_CURRENCY', 'EUR');

// Nombre de décimales pour les montants
define('DECIMAL_PLACES', 2);

// ================================================================
// PARAMÈTRES DE PAGINATION
// ================================================================

// Nombre d'éléments par page par défaut
define('DEFAULT_ITEMS_PER_PAGE', 50);

// Nombre maximum d'éléments par page
define('MAX_ITEMS_PER_PAGE', 200);

// ================================================================
// FIN DE LA CONFIGURATION
// ================================================================

// Créer les dossiers nécessaires s'ils n'existent pas
$requiredDirs = [LOG_DIR, BACKUP_DIR];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Démarrer la session uniquement en contexte web
if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
