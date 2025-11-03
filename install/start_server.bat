@echo off
title Serveur Web - Gestion de Caisse Regie
color 0A

echo ================================================================
echo              SERVEUR WEB - GESTION DE CAISSE REGIE
echo ================================================================
echo.
echo Demarrage du serveur web PHP integre...
echo.
echo Application disponible sur:
echo   http://localhost:8080
echo   http://127.0.0.1:8080
echo.
echo Pour arreter le serveur, appuyez sur Ctrl+C
echo.
echo ================================================================
echo.

REM Changer vers le répertoire parent (racine de l'application)
cd /d "%~dp0\.."

REM Vérifier que PHP est disponible
php --version >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERREUR] PHP n'est pas trouve dans le PATH
    echo Veuillez installer PHP ou l'ajouter au PATH systeme
    echo.
    pause
    exit /b 1
)

REM Démarrer le serveur PHP intégré
echo Demarrage du serveur sur le port 8080...
echo.
php -S localhost:8080 -t .

REM Si le serveur s'arrête
echo.
echo ================================================================
echo Le serveur s'est arrete.
echo ================================================================
pause