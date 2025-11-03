-- Script de configuration PostgreSQL pour l'application Caisse Régie
-- À exécuter en tant que superutilisateur PostgreSQL (postgres)

-- Paramètres par défaut (à modifier selon vos besoins)
-- Utilisateur: caisse_user
-- Base: caisse_regie
-- Mot de passe: à définir lors de l'installation

-- Création de l'utilisateur de l'application
DO $$
BEGIN
    -- Vérifier si l'utilisateur existe déjà
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'caisse_user') THEN
        CREATE USER caisse_user WITH PASSWORD 'caisse_password_123';
        RAISE NOTICE 'Utilisateur caisse_user créé';
    ELSE
        RAISE NOTICE 'Utilisateur caisse_user existe déjà';
    END IF;
END
$$;

-- Création de la base de données
DO $$
BEGIN
    -- PostgreSQL ne permet pas CREATE DATABASE dans un bloc DO
    -- Cette partie sera exécutée séparément
    RAISE NOTICE 'Création de la base de données...';
END
$$;

-- Cette commande doit être exécutée séparément
-- CREATE DATABASE caisse_regie OWNER caisse_user;

-- Attribution des privilèges
GRANT ALL PRIVILEGES ON DATABASE caisse_regie TO caisse_user;

-- Connexion à la nouvelle base pour configurer les privilèges
\c caisse_regie;

-- Octroyer tous les privilèges sur le schéma public
GRANT ALL ON SCHEMA public TO caisse_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO caisse_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO caisse_user;

-- Permettre la création d'objets dans le schéma public
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO caisse_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO caisse_user;

-- Configuration de sécurité recommandée
-- Limiter les connexions simultanées
ALTER USER caisse_user CONNECTION LIMIT 10;

-- Définir une base de données par défaut
ALTER USER caisse_user SET default_transaction_isolation = 'read committed';

-- Afficher les informations de connexion
SELECT 
    'Configuration terminée' as status,
    'caisse_user' as username,
    'caisse_regie' as database_name,
    '5432' as default_port;

-- Vérification des permissions
SELECT 
    schemaname,
    tablename,
    tableowner
FROM pg_tables 
WHERE schemaname = 'public';

-- Instructions pour la connexion
SELECT 'Pour vous connecter à la base de données :' as instructions
UNION ALL
SELECT 'psql -h localhost -U caisse_user -d caisse_regie'
UNION ALL
SELECT 'Ou dans votre application PHP :'
UNION ALL
SELECT 'Host: localhost, Port: 5432, Database: caisse_regie, User: caisse_user';