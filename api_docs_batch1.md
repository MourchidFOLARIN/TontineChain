# 🚀 Documentation API TontineChain - MIABE 2026

**Base URL (Production)** : `https://tonnine-benin-backend.onrender.com/api/v1`  
**Format** : JSON  
**Authentification** : Bearer Token (Sanctum)

---

## 🔐 1. Authentification & OTP

### `POST /auth/request-otp`
Demande l'envoi d'un code OTP (6 chiffres) par e-mail.
- **Paramètres (JSON)** :
  - `email` (string, required) : L'adresse e-mail de l'utilisateur.
- **Réponse (200)** :
  ```json
  { "message": "OTP envoyé avec succès", "debug_otp": "123456" }
  ```
- **Note** : Le `debug_otp` n'apparaît qu'en mode test. En prod, l'utilisateur reçoit un vrai e-mail.

### `POST /auth/verify-otp`
Vérifie le code et connecte l'utilisateur.
- **Paramètres (JSON)** :
  - `email` (string, required)
  - `otp` (string, required) : Le code reçu.
- **Réponse (200)** :
  ```json
  {
    "message": "Authentification réussie",
    "token": "1|abcde...",
    "user": { "id": "...", "full_name": "...", "score_confiance": 100 }
  }
  ```
- **Note** : Sauvegarder le `token` pour tous les appels suivants (Header: `Authorization: Bearer <token>`).

---

## 👤 2. Profil Utilisateur & KYC

### `GET /users/me`
Récupère les informations complètes de l'utilisateur connecté.
- **Headers** : `Authorization: Bearer <token>`
- **Réponse (200)** : Contient le nom, phone, profession, `kyc_status` (none/pending/verified), etc.

### `PATCH /users/me`
Met à jour les informations de base.
- **Paramètres (JSON)** :
  - `first_name`, `last_name`, `profession`, `npi` (Numéro Identification), `preferred_language` (fr/fon/yor).
- **Réponse (200)** : Profil mis à jour.

### `POST /users/me/kyc`
Envoie la photo de la pièce d'identité pour certification.
- **Paramètres (Multipart/Form-Data)** :
  - `document` (file, required) : Image (jpg/png).
- **Réponse (200)** :
  ```json
  {
    "message": "Document reçu",
    "status": "pending",
    "demo_notice": { "message": "Analyse OCR en cours par YAO..." }
  }
  ```

### `GET /users/me/score`
Récupère le score de confiance actuel et l'historique des incidents.
- **Réponse (200)** :
  ```json
  {
    "score_confiance": 80,
    "incidents": [ { "type": "late_payment", "description": "...", "occurred_at": "..." } ]
  }
  ```

### `GET /users/me/balance`
Statistiques financières personnelles.
- **Réponse (200)** :
  ```json
  {
    "total_cotise_fcfa": 50000,
    "total_recu_fcfa": 0,
    "expected_payouts_fcfa": 150000
  }
  ```
