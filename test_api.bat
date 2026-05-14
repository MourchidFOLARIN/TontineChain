@echo off
REM Script de lancement des tests API TontineChain pour Windows
REM Utilisation: test_api.bat [email_test]

echo 🚀 Lancement des tests API TontineChain
echo.

if "%~1"=="" (
    echo Utilisation: test_api.bat votre.email@test.com
    echo Ou avec l'email par défaut: test_api.bat
    echo.
    set TEST_EMAIL=test@example.com
) else (
    set TEST_EMAIL=%~1
)

echo Configuration:
echo - Base URL: https://tonnine-benin-backend.onrender.com/api/v1
echo - Email de test: %TEST_EMAIL%
echo.

echo ⚠️  Ce script nécessite Git Bash ou WSL pour fonctionner sous Windows
echo.
echo Installation recommandée:
echo 1. Git Bash: https://gitforwindows.org/
echo 2. Ou WSL: wsl --install
echo.
echo Puis exécuter: bash test_api.sh %TEST_EMAIL%
echo.

pause