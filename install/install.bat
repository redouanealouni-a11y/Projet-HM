@echo off
echo ================================================================
echo    INSTALLATION DE L'APPLICATION CAISSE REGIE PHP/POSTGRESQL
echo ================================================================
echo.

REM Vérification des droits administrateur
net session >nul 2>&1
if %errorLevel% == 0 (
    echo [OK] Droits administrateur detectes
) else (
    echo [ERREUR] Ce script necessite des droits administrateur
    echo Clic droit sur install.bat et "Executer en tant qu'administrateur"
    pause
    exit /b 1
)

echo.
echo Etape 1/6 : Verification de PostgreSQL...
echo ----------------------------------------

REM Vérifier si PostgreSQL est installé
pg_config --version >nul 2>&1
if %errorLevel% == 0 (
    echo [OK] PostgreSQL est installe
    for /f "tokens=*" %%a in ('pg_config --version') do echo Version: %%a
) else (
    echo [INFO] PostgreSQL n'est pas installe ou pas dans le PATH
    echo Veuillez installer PostgreSQL depuis: https://www.postgresql.org/download/windows/
    echo Version recommandee: PostgreSQL 14 ou superieur
    pause
    echo Appuyez sur une touche une fois PostgreSQL installe...
    pause
)

echo.
echo Etape 2/6 : Verification de PHP...
echo ----------------------------------

REM Vérifier si PHP est installé
php --version >nul 2>&1
if %errorLevel% == 0 (
    echo [OK] PHP est installe
    for /f "tokens=*" %%a in ('php --version ^| findstr "PHP"') do echo %%a
    
    REM Vérifier l'extension PostgreSQL
    php -m | findstr pgsql >nul 2>&1
    if %errorLevel% == 0 (
        echo [OK] Extension PHP PostgreSQL detectee
    ) else (
        echo [ATTENTION] Extension PHP PostgreSQL manquante
        echo Veuillez activer l'extension pgsql dans php.ini
        echo Decommentez la ligne: extension=pgsql
        pause
    )
    
    REM Vérifier l'extension PDO PostgreSQL
    php -m | findstr pdo_pgsql >nul 2>&1
    if %errorLevel__ == 0 (
        echo [OK] Extension PHP PDO PostgreSQL detectee
    ) else (
        echo [ATTENTION] Extension PHP PDO PostgreSQL manquante
        echo Veuillez activer l'extension pdo_pgsql dans php.ini
        echo Decommentez la ligne: extension=pdo_pgsql
        pause
    )
) else (
    echo [INFO] PHP n'est pas installe ou pas dans le PATH
    echo Veuillez installer PHP depuis: https://windows.php.net/download/
    echo Version recommandee: PHP 8.0 ou superieur
    pause
)

echo.
echo Etape 3/6 : Configuration de la base de donnees...
echo --------------------------------------------------

set /p DB_HOST="Adresse du serveur PostgreSQL [localhost]: "
if "%DB_HOST%"=="" set DB_HOST=localhost

set /p DB_PORT="Port PostgreSQL [5432]: "
if "%DB_PORT%"=="" set DB_PORT=5432

set /p DB_NAME="Nom de la base de donnees [caisse_regie]: "
if "%DB_NAME%"=="" set DB_NAME=caisse_regie

set /p DB_USER="Utilisateur PostgreSQL [caisse_user]: "
if "%DB_USER%"=="" set DB_USER=caisse_user

set /p DB_PASS="Mot de passe utilisateur: "

echo.
echo Configuration:
echo - Serveur: %DB_HOST%:%DB_PORT%
echo - Base: %DB_NAME%
echo - Utilisateur: %DB_USER%
echo.

echo Etape 4/6 : Creation de la base de donnees...
echo ---------------------------------------------

REM Créer la base de données et l'utilisateur
echo CREATE USER %DB_USER% WITH PASSWORD '%DB_PASS%'; | psql -h %DB_HOST% -p %DB_PORT% -U postgres
if %errorLevel% == 0 (
    echo [OK] Utilisateur %DB_USER% cree
) else (
    echo [INFO] Utilisateur existe peut-etre deja
)

echo CREATE DATABASE %DB_NAME% OWNER %DB_USER%; | psql -h %DB_HOST% -p %DB_PORT% -U postgres
if %errorLevel% == 0 (
    echo [OK] Base de donnees %DB_NAME% creee
) else (
    echo [INFO] Base de donnees existe peut-etre deja
)

echo GRANT ALL PRIVILEGES ON DATABASE %DB_NAME% TO %DB_USER%; | psql -h %DB_HOST% -p %DB_PORT% -U postgres

echo.
echo Etape 5/6 : Creation des tables...
echo ----------------------------------

REM Exécuter le script de création des tables
psql -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_NAME% -f "../database/schema.sql"
if %errorLevel__ == 0 (
    echo [OK] Tables creees avec succes
) else (
    echo [ERREUR] Probleme lors de la creation des tables
    pause
)

echo.
echo Etape 6/6 : Configuration de l'application...
echo ---------------------------------------------

REM Créer le fichier de configuration personnalisé
(
echo ^<?php
echo // Configuration personnalisée générée par l'installateur
echo define^('DB_HOST', '%DB_HOST%'^);
echo define^('DB_PORT', '%DB_PORT%'^);
echo define^('DB_NAME', '%DB_NAME%'^);
echo define^('DB_USER', '%DB_USER%'^);
echo define^('DB_PASS', '%DB_PASS%'^);
echo ?^>
) > "../config/config_local.php"

echo [OK] Fichier de configuration cree

REM Créer un script de démarrage
(
echo @echo off
echo echo Demarrage du serveur web PHP...
echo echo Application disponible sur: http://localhost:8080
echo echo Appuyez sur Ctrl+C pour arreter le serveur
echo php -S localhost:8080 -t "../"
echo pause
) > "start_server.bat"

echo [OK] Script de demarrage cree

echo.
echo ================================================================
echo                    INSTALLATION TERMINEE
echo ================================================================
echo.
echo L'application est maintenant installee et configuree.
echo.
echo Pour demarrer l'application:
echo 1. Double-cliquez sur 'start_server.bat'
echo 2. Ouvrez votre navigateur sur: http://localhost:8080
echo.
echo Fichiers importants:
echo - Configuration: config/config_local.php
echo - Demarrage: install/start_server.bat
echo - Logs: Voir la console du serveur PHP
echo.
echo En cas de probleme:
echo - Verifiez que PostgreSQL est demarre
echo - Verifiez que les extensions PHP sont activees
echo - Consultez le fichier README.md
echo.
echo Voulez-vous demarrer le serveur maintenant ? (o/n)
set /p START_NOW=
if /i "%START_NOW%"=="o" (
    start start_server.bat
)

echo.
echo Installation terminee avec succes !
pause