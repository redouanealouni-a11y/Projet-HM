<?php

/**
 * Core configuration and bootstrap file for the application.
 *
 * This file is responsible for:
 * - Loading the local configuration override.
 * - Defining default configuration constants.
 * - Setting up error reporting and session management.
 * - Including necessary class and helper files.
 */

// --- Configuration Loading ---

// Load local configuration override if it exists.
if (file_exists(__DIR__ . '/config_local.php')) {
    require_once __DIR__ . '/config_local.php';
}

// Define default database connection constants if not defined in local config.
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', '5432');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'caisse_regie');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'postgres');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'admin');
}

// --- Application Settings ---

// Define core application settings if they haven't been set.
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Gestion de Caisse Régie');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '2.0.0');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Define security and CORS settings.
if (!defined('APP_SECRET_KEY')) {
    define('APP_SECRET_KEY', 'default_secret_key_please_change');
}
if (!defined('ALLOWED_ORIGIN')) {
    define('ALLOWED_ORIGIN', '*');
}

// Set the default timezone if not already set in php.ini.
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Europe/Paris');
}

// --- Includes ---

// Include the new separated files for database and helper functions.
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

// --- Environment Setup ---

// Configure session handling only for web requests.
if (php_sapi_name() !== 'cli') {
    ini_set('session.cookie_lifetime', 86400); // 24 hours
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Configure error reporting based on the debug flag.
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
