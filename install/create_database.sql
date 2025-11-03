-- Script de création rapide de la base de données
-- Application Gestion de Caisse Régie PHP/PostgreSQL
-- 
-- Instructions d'utilisation :
-- 1. Se connecter en tant que superutilisateur PostgreSQL :
--    psql -U postgres
-- 2. Exécuter ce script :
--    \i install/create_database.sql
-- 3. Puis exécuter le schema :
--    \c caisse_regie caisse_user
--    \i database/schema.sql

-- Variables par défaut (modifiez selon vos besoins)
-- Nom de la base : caisse_regie
-- Utilisateur : caisse_user  
-- Mot de passe : caisse_password_123

-- Affichage des informations
\echo '================================================================'
\echo '    CREATION DE LA BASE DE DONNEES - CAISSE REGIE'
\echo '================================================================'
\echo ''

-- Supprimer la base si elle existe (ATTENTION : perte de données)
DROP DATABASE IF EXISTS caisse_regie;
\echo 'Base de donnees supprimee si elle existait'

-- Supprimer l'utilisateur s'il existe
DROP USER IF EXISTS caisse_user;
\echo 'Utilisateur supprime s''il existait'

-- Créer l'utilisateur
CREATE USER caisse_user WITH 
    PASSWORD 'caisse_password_123'
    CREATEDB
    LOGIN;
\echo 'Utilisateur caisse_user cree'

-- Créer la base de données
CREATE DATABASE caisse_regie 
    WITH OWNER caisse_user
    ENCODING 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8'
    TEMPLATE template0;
\echo 'Base de donnees caisse_regie creee'

-- Accorder tous les privilèges sur la base
GRANT ALL PRIVILEGES ON DATABASE caisse_regie TO caisse_user;
\echo 'Privileges accordes'

-- Se connecter à la nouvelle base
-- La connexion doit être faite manuellement après ce script.
-- \c caisse_regie caisse_user

-- La création des extensions et tables est gérée par `database/schema.sql`.
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
-- \echo 'Extension UUID activee'

-- Vérifier la connexion
-- SELECT
--     current_database() as database_name,
--     current_user as connected_user,
--     version() as postgresql_version;

\echo ''
\echo '================================================================'
\echo '                    CREATION TERMINEE'
\echo '================================================================'
\echo 'Base de donnees : caisse_regie'
\echo 'Utilisateur     : caisse_user'  
\echo 'Mot de passe    : caisse_password_123'
\echo 'Port            : 5432 (par defaut)'
\echo ''
\echo 'Prochaines etapes :'
\echo '1. Executer le schema : \\i database/schema.sql'
\echo '2. Configurer l''application dans config/database.php'
\echo '3. Demarrer le serveur web'
\echo '================================================================'