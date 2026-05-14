@echo off
REM Script de vérification de l'intégrité de la suite de tests
REM Utilisation: check_tests.bat

echo 🔍 Vérification de la suite de tests TontineChain
echo ==============================================
echo.

set ALL_PRESENT=true

REM Liste des fichiers requis
set REQUIRED_FILES=TEST_GUIDE.md TontineChain_API_Tests.postman_collection.json test_api.sh test_api.ps1 test_api.bat README_TESTING.md check_tests.sh

for %%f in (%REQUIRED_FILES%) do (
    if exist "%%f" (
        echo ✅ %%f - Présent
    ) else (
        echo ❌ %%f - MANQUANT
        set ALL_PRESENT=false
    )
)

echo.
echo 📊 Résumé:
if "%ALL_PRESENT%"=="true" (
    echo ✅ Tous les fichiers de test sont présents
    echo.
    echo 🚀 Prêt à tester !
    echo    - PowerShell: .\test_api.ps1 -Email votre.email@test.com
    echo    - Bash: bash test_api.sh votre.email@test.com
    echo    - Postman: Importer TontineChain_API_Tests.postman_collection.json
    echo    - Manuel: Suivre TEST_GUIDE.md
) else (
    echo ❌ Certains fichiers sont manquants
    echo    Veuillez régénérer la suite de tests
    exit /b 1
)

echo.
echo 🔗 Liens utiles:
echo    - API: https://tonnine-benin-backend.onrender.com/api/v1
echo    - Docs: https://tonnine-benin-backend.onrender.com/api/documentation
echo    - Health: https://tonnine-benin-backend.onrender.com/api/v1/health

pause