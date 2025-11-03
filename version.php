<?php
/**
 * Informations de version du projet YAMO
 * 
 * @package YAMO
 * @author MiniMax Agent
 * @version 1.0.0
 * @date 2025-11-03
 */

// Version du projet
define('YAMO_VERSION', '1.0.0');

// Date de version
define('YAMO_VERSION_DATE', '2025-11-03');

// Informations du projet
define('YAMO_NAME', 'YAMO - Gestion de Caisse Régie');
define('YAMO_DESCRIPTION', 'Application web de gestion de caisse régie développée en PHP/PostgreSQL');

// Configuration technique
define('YAMO_PHP_MIN_VERSION', '7.4');
define('YAMO_DB_TYPE', 'PostgreSQL');
define('YAMO_DB_MIN_VERSION', '12');

// Headers de version
header('X-YAMO-Version: ' . YAMO_VERSION);
header('X-YAMO-Build: ' . YAMO_VERSION_DATE);