## 👥 3. Gestion des Groupes (Tontines)

### `GET /groups`
Liste tous les groupes auxquels l'utilisateur participe.
- **Réponse (200)** : Tableau d'objets groupes (nom, montant, fréquence, statut).

### `POST /groups`
Crée un nouveau groupe de tontine.
- **Paramètres (JSON)** :
  - `name` (string) : Nom du groupe.
  - `contribution_amount` (float) : Montant par cycle (ex: 10000).
  - `frequency` (string) : `daily`, `weekly` ou `monthly`.
  - `max_members` (int) : Nombre total de participants prévus.
  - `description` (string, optional).
- **Réponse (201)** : Groupe créé avec statut `pending`.

### `GET /groups/{group_id}`
Détails d'un groupe spécifique, incluant la liste des membres.

### `POST /groups/{group_id}/invite`
Invite un nouveau membre par son e-mail ou son téléphone.
- **Paramètres (JSON)** :
  - `email` (string, optional)
  - `phone` (string, optional)
- **Réponse (200)** : Invitation envoyée.

### `POST /groups/{group_id}/join`
Rejoindre un groupe (si on a reçu une invitation).
- **Réponse (200)** : Utilisateur ajouté au groupe.

### `POST /groups/{group_id}/start`
Démarre officiellement la tontine.
- **Conditions** : Le groupe doit être complet ou le créateur décide de lancer.
- **Actions Backend** : Génère les ordres de passage, déploie le contrat Blockchain, génère le PDF et l'envoie par e-mail.
- **Réponse (200)** : Groupe passe en statut `active`.

### `GET /groups/{group_id}/contract`
Télécharge le contrat PDF de la tontine.
- **Réponse** : Fichier PDF.

### `GET /users/leaderboard` (Public)
Top 10 des membres les plus fiables de la plateforme.
- **Réponse (200)** : Liste des noms et scores.
