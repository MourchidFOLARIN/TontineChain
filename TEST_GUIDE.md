# Guide de Test Complet de l'API TontineChain

## Vue d'ensemble
Ce guide fournit un déroulement complet pour tester l'API TontineChain de manière systématique et professionnelle.

## 1. Préparation de l'environnement

### Variables d'environnement
```bash
# URL de base de l'API
BASE_URL="https://tonnine-benin-backend.onrender.com/api/v1"

# Données de test
TEST_EMAIL="test@example.com"
TEST_PHONE="+22997000000"
TEST_LOCALE="fr"

# Tokens (à récupérer pendant les tests)
ACCESS_TOKEN=""
```

### Outils recommandés
- **Postman** ou **Insomnia** : Interface graphique pour les tests manuels
- **curl** ou **HTTPie** : Tests en ligne de commande
- **Newman** : Tests automatisés avec collections Postman
- **k6** : Tests de performance

### Configuration de test
- Vérifier que l'email de test peut recevoir des OTP
- S'assurer que le serveur est accessible
- Préparer des données de test cohérentes

---

## 2. Tests de connectivité

### Test 1 : Health Check
**Objectif** : Vérifier que l'API répond et est opérationnelle

**Requête** :
```http
GET /api/v1/health HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Accept: application/json
```

**Résultat attendu** :
```json
{
  "status": "ok",
  "message": "Service is healthy"
}
```

**Cas d'échec** :
- Status ≠ 200 : Problème serveur
- Timeout : Serveur indisponible

---

## 3. Tests d'authentification

### Test 2 : Demande d'OTP valide
**Objectif** : Tester l'envoi d'OTP avec des données valides

**Requête** :
```http
POST /api/v1/auth/request-otp HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Content-Type: application/json

{
  "email": "test@example.com",
  "phone": "+22997000000",
  "locale": "fr"
}
```

**Résultat attendu** :
```json
{
  "status": "success",
  "message": "Le code OTP a été envoyé à votre adresse email...",
  "email": "test@example.com"
}
```

**Cas d'échec** :
- Email invalide : 422
- Serveur SMTP down : 500

### Test 3 : Vérification OTP valide
**Objectif** : Tester la connexion avec un code OTP correct

**Requête** :
```http
POST /api/v1/auth/verify-otp HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Content-Type: application/json

{
  "email": "test@example.com",
  "code": "123456"
}
```

**Résultat attendu** :
```json
{
  "access_token": "1|abcdef1234567890",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "email": "test@example.com",
    "full_name": "Membre",
    "phone": null
  },
  "needs_profile_completion": true
}
```

**Actions post-test** :
- Sauvegarder `ACCESS_TOKEN` pour les tests suivants

---

## 4. Tests CRUD

### Test 4 : Création d'un groupe
**Objectif** : Tester la création de ressource

**Requête** :
```http
POST /api/v1/groups HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Authorization: Bearer {ACCESS_TOKEN}
Content-Type: application/json

{
  "name": "Tontine Test",
  "contribution_amount": 5000,
  "max_members": 5,
  "frequency": "monthly",
  "payout_method": "sequential",
  "insurance_opt_in": true
}
```

**Résultat attendu** :
- Status : 201
- Corps : Objet groupe créé avec ID

### Test 5 : Lecture d'un groupe
**Objectif** : Tester la récupération de ressource

**Requête** :
```http
GET /api/v1/groups/{group_id} HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Authorization: Bearer {ACCESS_TOKEN}
```

**Résultat attendu** :
- Status : 200
- Corps : Détails du groupe

### Test 6 : Mise à jour du profil utilisateur
**Objectif** : Tester la modification de ressource

**Requête** :
```http
PATCH /api/v1/users/me HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Authorization: Bearer {ACCESS_TOKEN}
Content-Type: application/json

{
  "full_name": "Jean Dupont",
  "phone": "+22997001122"
}
```

**Résultat attendu** :
- Status : 200
- Corps : Profil mis à jour

---

## 5. Tests de validation des données

### Test 7 : Email invalide dans request-otp
**Objectif** : Vérifier la validation des champs

**Requête** :
```http
POST /api/v1/auth/request-otp HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Content-Type: application/json

{
  "email": "invalid-email",
  "phone": "+22997000000"
}
```

**Résultat attendu** :
- Status : 422
- Corps : Erreurs de validation

### Test 8 : Code OTP trop court
**Objectif** : Tester la validation de longueur

**Requête** :
```http
POST /api/v1/auth/verify-otp HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Content-Type: application/json

{
  "email": "test@example.com",
  "code": "123"
}
```

**Résultat attendu** :
- Status : 422

---

## 6. Tests de gestion d'erreurs

### Test 9 : Accès sans authentification
**Objectif** : Vérifier la protection des routes

**Requête** :
```http
GET /api/v1/users/me HTTP/1.1
Host: tonnine-benin-backend.onrender.com
```

**Résultat attendu** :
- Status : 401

### Test 10 : Ressource inexistante
**Objectif** : Tester les erreurs 404

**Requête** :
```http
GET /api/v1/groups/999999 HTTP/1.1
Host: tonnine-benin-backend.onrender.com
Authorization: Bearer {ACCESS_TOKEN}
```

**Résultat attendu** :
- Status : 404

### Test 11 : Tentatives OTP excessives
**Objectif** : Tester la limitation de taux

**Requête** : 4 tentatives avec code faux
```http
POST /api/v1/auth/verify-otp HTTP/1.1
// ... avec code incorrect
```

**Résultat attendu** :
- Status : 429 (Too Many Requests)

---

## 7. Tests de logique métier

### Scénario complet : Flux utilisateur

1. **Inscription/Connexion**
   - POST /auth/request-otp
   - POST /auth/verify-otp
   - Récupérer token

2. **Configuration du profil**
   - PATCH /users/me
   - Vérifier que needs_profile_completion devient false

3. **Création d'un groupe**
   - POST /groups
   - Récupérer group_id

4. **Gestion du groupe**
   - GET /groups/{group_id}
   - POST /groups/{group_id}/invite (si applicable)
   - GET /groups/{group_id}/stats

5. **Participation aux cotisations**
   - GET /contributions/pending
   - POST /contributions/{contribution}/pay (si applicable)

6. **Interaction sociale**
   - GET /groups/{group_id}/messages
   - POST /groups/{group_id}/messages

7. **IA Assistant**
   - POST /ai/chat avec différentes questions

---

## 8. Tests de performance

### Test 12 : Charge de base
**Objectif** : Mesurer les temps de réponse

**Commande** :
```bash
# 10 requêtes séquentielles
for i in {1..10}; do
  curl -w "@curl-format.txt" -o /dev/null -s \
    "https://tonnine-benin-backend.onrender.com/api/v1/health"
done
```

**Métriques attendues** :
- Temps de réponse moyen < 1000ms
- Aucun échec (status ≠ 200)

### Test 13 : Authentification sous charge
**Objectif** : Tester l'authentification avec charge

**Commande** :
```bash
# 5 requêtes concurrentes
ab -n 5 -c 5 \
  -H "Authorization: Bearer {ACCESS_TOKEN}" \
  "https://tonnine-benin-backend.onrender.com/api/v1/users/me"
```

---

## 9. Rapport de test

### Structure du rapport

#### Résumé exécutif
- Date du test
- Environnement testé
- Statut global (Réussi/Échoué)

#### Résultats détaillés
| Test | Statut | Temps | Notes |
|------|--------|-------|-------|
| Health Check | ✅ | 150ms | OK |
| Request OTP | ✅ | 200ms | Email envoyé |
| ... | ... | ... | ... |

#### Bugs identifiés
- Description du problème
- Étapes pour reproduire
- Impact estimé
- Priorité (Critique/Majeur/Mineur)

#### Recommandations
- Améliorations suggérées
- Tests supplémentaires à envisager

### Exemple de rapport

```
RAPPORT DE TEST API TONTINECHAIN
================================

Date: 2026-05-14
Environnement: Production (Render)
Testeur: [Votre nom]

RÉSUMÉ:
- Tests exécutés: 15
- Réussis: 14
- Échoués: 1
- Temps total: 45 secondes

BUGS CRITIQUES:
1. Endpoint /ai/chat retourne 500 en cas de charge élevée

RECOMMANDATIONS:
1. Implémenter un cache pour les réponses IA
2. Ajouter des tests d'intégration automatisés
```

---

## 10. Collection Postman

Pour faciliter les tests, créer une collection Postman avec :

### Variables de collection
- `base_url`: `https://tonnine-benin-backend.onrender.com/api/v1`
- `access_token`: (à définir dynamiquement)

### Structure des dossiers
```
📁 TontineChain API Tests
├── 📁 1. Connectivité
│   └── Health Check
├── 📁 2. Authentification
│   ├── Request OTP
│   └── Verify OTP
├── 📁 3. CRUD
│   ├── Create Group
│   ├── Read Group
│   └── Update Profile
├── 📁 4. Validation
│   ├── Invalid Email
│   └── Invalid Code
├── 📁 5. Erreurs
│   ├── Unauthorized
│   └── Not Found
├── 📁 6. Logique métier
│   └── Flux complet
└── 📁 7. Performance
    └── Load Test
```

### Tests automatisés dans Postman
```javascript
// Exemple de test pour vérifier le status
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Sauvegarder le token
if (pm.response.code === 200 && pm.response.json().access_token) {
    pm.collectionVariables.set("access_token", pm.response.json().access_token);
}
```

---

## 11. Checklist finale

- [ ] Environnement préparé
- [ ] Tests de connectivité passés
- [ ] Authentification fonctionnelle
- [ ] CRUD opérationnel
- [ ] Validation des données correcte
- [ ] Gestion d'erreurs appropriée
- [ ] Logique métier validée
- [ ] Performance acceptable
- [ ] Rapport documenté
- [ ] Collection Postman créée

---

## Notes importantes

1. **Sécurité** : Ne jamais commiter de vrais tokens ou mots de passe
2. **Données de test** : Utiliser des emails temporaires pour les tests
3. **Rate limiting** : Respecter les limites pour éviter les blocages
4. **Nettoyage** : Supprimer les données de test après utilisation
5. **Documentation** : Tenir à jour ce guide avec les changements d'API

---

*Ce guide est conçu pour être évolutif. Mettez-le à jour à chaque modification majeure de l'API.*