#!/bin/bash
# Script de vérification de l'intégrité de la suite de tests
# Utilisation: ./check_tests.sh

echo "🔍 Vérification de la suite de tests TontineChain"
echo "=============================================="

# Liste des fichiers requis
REQUIRED_FILES=(
    "TEST_GUIDE.md"
    "TontineChain_API_Tests.postman_collection.json"
    "test_api.sh"
    "test_api.ps1"
    "test_api.bat"
    "README_TESTING.md"
)

ALL_PRESENT=true

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file - Présent"
    else
        echo "❌ $file - MANQUANT"
        ALL_PRESENT=false
    fi
done

echo ""
echo "📊 Résumé:"
if [ "$ALL_PRESENT" = true ]; then
    echo "✅ Tous les fichiers de test sont présents"
    echo ""
    echo "🚀 Prêt à tester !"
    echo "   - Bash: ./test_api.sh votre.email@test.com"
    echo "   - PowerShell: .\test_api.ps1 -Email votre.email@test.com"
    echo "   - Postman: Importer TontineChain_API_Tests.postman_collection.json"
    echo "   - Manuel: Suivre TEST_GUIDE.md"
else
    echo "❌ Certains fichiers sont manquants"
    echo "   Veuillez régénérer la suite de tests"
    exit 1
fi

echo ""
echo "🔗 Liens utiles:"
echo "   - API: https://tonnine-benin-backend.onrender.com/api/v1"
echo "   - Docs: https://tonnine-benin-backend.onrender.com/api/documentation"
echo "   - Health: https://tonnine-benin-backend.onrender.com/api/v1/health"