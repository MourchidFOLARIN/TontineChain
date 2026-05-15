## 💬 6. Messagerie & Chat de Groupe

### `GET /groups/{group_id}/messages`
Récupère l'historique des messages du groupe (système et membres).
- **Réponse (200)** : Liste ordonnée par date.

### `POST /groups/{group_id}/messages`
Envoie un message dans le chat.
- **Paramètres (JSON)** : `content` (string).
- **Réponse (201)** : Message créé.

---

## 🤖 7. Assistant IA (YAO)

### `POST /ai/chat`
Discuter avec YAO pour obtenir des infos sur son compte ou la blockchain.
- **Paramètres (JSON)** :
  - `message` (string) : Ex: "Quel est mon score ?", "Akwé nabi ?" (Combien d'argent ?).
  - `locale` (string, optional) : `fr`, `fon` ou `yor`.
- **Réponse (200)** :
  ```json
  { "assistant": "YAO", "message": "Ton score est de 80...", "demo_notice": { ... } }
  ```

---

## 🔔 8. Notifications

### `GET /notifications`
Liste toutes les notifications personnelles (nouvel invité, rappel de paiement, etc.).

### `PATCH /notifications/{notification_id}/read`
Marque une notification comme lue.

---

## 📈 9. Enchères (Bidding)
*Utile uniquement si le mode de paiement du groupe est 'bidding'*

### `GET /groups/{group_id}/bids`
Liste des enchères en cours pour le cycle actuel.

### `POST /groups/{group_id}/bid`
Soumettre une enchère pour essayer de ramasser le pot plus tôt.
- **Paramètres (JSON)** : `amount` (float).
