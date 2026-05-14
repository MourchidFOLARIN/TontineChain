# 🧪 TontineChain API Testing Suite

Suite complète d'outils pour tester l'API TontineChain de manière professionnelle et automatisée.

## 📋 Contenu de la suite

- **`TEST_GUIDE.md`** : Guide complet de test manuel avec tous les scénarios
- **`TontineChain_API_Tests.postman_collection.json`** : Collection Postman avec tests automatisés
- **`test_api.sh`** : Script bash pour tests automatisés (Linux/macOS)
- **`test_api.ps1`** : Script PowerShell pour tests automatisés (Windows)
- **`test_api.bat`** : Lanceur Windows pour les tests
- **`check_tests.sh`** / **`check_tests.bat`** : Vérification de l'intégrité de la suite
- **`README_TESTING.md`** : Ce guide d'utilisation

## 🚀 Démarrage rapide

### Vérification préalable
```bash
# Linux/macOS
./check_tests.sh

# Windows
check_tests.bat
```

### Windows (PowerShell)
```powershell
# Lancer les tests avec votre email
.\test_api.ps1 -Email "votre.email@test.com"

# Ou utiliser l'email par défaut
.\test_api.ps1
```

### Linux/macOS (Bash)
```bash
# Rendre le script exécutable
chmod +x test_api.sh

# Lancer les tests
./test_api.sh votre.email@test.com
```

### Avec Postman
1. Importer `TontineChain_API_Tests.postman_collection.json`
2. Configurer les variables de collection
3. Exécuter la collection complète

### Tests manuels
Suivre le guide détaillé dans `TEST_GUIDE.md`

## 🔧 Prérequis

### Pour tous les environnements
- Connexion internet
- Email valide pour recevoir les OTP

### Windows
- PowerShell 5.1+ (installé par défaut)
- Execution Policy: `RemoteSigned` ou `Unrestricted`

### Linux/macOS
- Bash shell
- curl
- jq (pour le formatage JSON)

### Postman
- Application Postman Desktop ou Web
- Version récente recommandée

## 📊 Couverture des tests

### Endpoints testés
- ✅ **Health Check** (`GET /health`)
- ✅ **OTP Request** (`POST /auth/request-otp`)
- ✅ **OTP Verification** (`POST /auth/verify-otp`)
- ✅ **User Profile** (`GET /users/me`, `PATCH /users/me`)
- ✅ **Groups** (`POST /groups`, `GET /groups/{id}`)
- ✅ **AI Chat** (`POST /ai/chat`)

### Scénarios couverts
- 🔐 **Authentification** : OTP complet avec vérification
- 👤 **Gestion utilisateur** : Profil et mises à jour
- 👥 **Groupes** : Création et consultation
- 🤖 **IA** : Chat conversationnel
- 🚫 **Sécurité** : Accès non autorisé, validation des données
- ⚡ **Performance** : Tests de charge de base

### Types de tests
- **Fonctionnels** : Vérification du comportement métier
- **Sécurité** : Authentification et autorisation
- **Validation** : Format des données et contraintes
- **Erreurs** : Gestion des cas d'erreur

## 🔍 Dépannage

### Problèmes courants

#### PowerShell - Execution Policy
```powershell
# Si vous obtenez une erreur d'exécution
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

#### Bash - Permissions
```bash
# Si le script n'est pas exécutable
chmod +x test_api.sh
```

#### Email OTP non reçu
- Vérifier le dossier spam
- Utiliser un email Gmail ou Outlook
- Attendre 1-2 minutes pour la livraison

#### Erreurs de connexion
- Vérifier la connexion internet
- Confirmer que l'API est en ligne : `curl https://tonnine-benin-backend.onrender.com/api/v1/health`

#### Postman - Import échoue
- Utiliser la dernière version de Postman
- Importer via "File > Import > Upload Files"

### Logs et débogage

Les scripts fournissent des logs détaillés :
- ✅ **Succès** : Opérations réussies
- ❌ **Erreurs** : Problèmes détectés
- ⚠️ **Avertissements** : Actions manuelles requises

### Support

Si vous rencontrez des problèmes :
1. Vérifier les logs pour les détails d'erreur
2. Consulter `TEST_GUIDE.md` pour les tests manuels
3. Vérifier le statut de l'API sur Render

## 📊 Couverture des tests

### ✅ Tests automatisés (script bash)
- Health check
- Authentification OTP (avec saisie manuelle du code)
- Récupération du profil utilisateur
- Création de groupe
- Lecture de groupe
- Mise à jour du profil
- Chat IA
- Tests de sécurité (accès non autorisé)
- Validation des données

### ✅ Tests Postman
- Tous les tests du script bash
- Tests de performance
- Tests de charge
- Validation automatique des réponses
- Sauvegarde automatique des tokens

### ✅ Tests manuels (guide)
- Scénarios métier complets
- Tests de performance avancés
- Tests de régression
- Documentation des résultats

## 🔧 Prérequis

### Pour le script bash
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install curl jq

# macOS
brew install curl jq

# Windows (avec WSL ou Git Bash)
# curl est généralement disponible
# jq: https://stedolan.github.io/jq/download/
```

### Pour Postman
- [Télécharger Postman](https://www.postman.com/downloads/)
- Importer la collection JSON

## 📝 Variables d'environnement

| Variable | Valeur par défaut | Description |
|----------|------------------|-------------|
| `BASE_URL` | `https://tonnine-benin-backend.onrender.com/api/v1` | URL de base de l'API |
| `TEST_EMAIL` | `test@example.com` | Email pour les tests |
| `TEST_PHONE` | `+22997000000` | Téléphone pour les tests |

## 🎯 Scénarios de test couverts

### 1. Connectivité
- Health check de l'API
- Vérification de la disponibilité

### 2. Authentification
- Demande d'OTP par email
- Vérification du code OTP
- Gestion des tokens Bearer
- Tests d'échec d'authentification

### 3. CRUD Operations
- Création de groupes
- Lecture des données
- Mise à jour des profils
- Gestion des erreurs 404

### 4. Validation des données
- Emails invalides
- Codes OTP malformés
- Champs obligatoires manquants
- Types de données incorrects

### 5. Gestion d'erreurs
- Codes HTTP appropriés (400, 401, 403, 404, 422, 500)
- Messages d'erreur clairs
- Rate limiting (tentatives OTP)

### 6. Logique métier
- Flux complet utilisateur
- Interactions groupe/membre
- Système de cotisations
- Chat IA intégré

### 7. Performance
- Temps de réponse
- Tests de charge basiques
- Utilisation mémoire

## 📈 Rapport de test

### Structure recommandée
```
RAPPORT DE TEST API TONTINECHAIN
================================

Date: YYYY-MM-DD
Environnement: Production
Testeur: [Votre nom]

RÉSUMÉ:
- Tests exécutés: X
- Réussis: X
- Échoués: X
- Temps total: Xs

BUGS IDENTIFIÉS:
1. [Description]
2. [Description]

RECOMMANDATIONS:
1. [Amélioration]
2. [Amélioration]
```

## 🔒 Sécurité des tests

- **Ne jamais commiter** de vrais tokens ou mots de passe
- Utiliser des **emails temporaires** pour les tests
- **Respecter les limites** de taux pour éviter les blocages
- **Nettoyer** les données de test après utilisation

## 🚨 Dépannage

### Script bash ne fonctionne pas
```bash
# Vérifier les permissions
ls -la test_api.sh

# Rendre exécutable
chmod +x test_api.sh

# Vérifier les dépendances
which curl
which jq
```

### Postman ne sauvegarde pas les variables
- Vérifier que les tests sont activés dans Postman
- Regarder la console Postman pour les erreurs JavaScript

### API retourne des erreurs 500
- Vérifier que l'API est en ligne : `curl https://tonnine-benin-backend.onrender.com/api/v1/health`
- Consulter les logs du serveur Render

## 📚 Ressources supplémentaires

- [Documentation Swagger](https://tonnine-benin-backend.onrender.com/api/documentation)
- [Collection Postman officielle](https://www.postman.com/)
- [Guide des tests API REST](https://restfulapi.net/testing/)

## 🤝 Contribution

Pour améliorer cette suite de tests :
1. Signaler les bugs dans les tests
2. Proposer de nouveaux scénarios
3. Améliorer la documentation
4. Ajouter des tests de performance

---

*Dernière mise à jour : $(date)*