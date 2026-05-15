## 💰 4. Cotisations & Paiements

### `GET /contributions/pending`
Récupère la liste de toutes les cotisations que l'utilisateur doit payer (statut `pending`, `late` ou `processing`).
- **Réponse (200)** : Tableau de cotisations.

### `POST /contributions/{contribution_id}/pay`
Initialise un paiement via **FedaPay**.
- **Réponse (200)** :
  ```json
  {
    "url": "https://checkout.fedapay.com/...",
    "transaction_id": "12345",
    "message": "Veuillez finaliser le paiement sur FedaPay"
  }
  ```
- **Action Front** : Rediriger l'utilisateur vers l'URL fournie. Après le paiement, FedaPay redirigera vers l'App et le Backend recevra le Webhook.

---

## 🏦 5. Ramassages (Payouts) & Incidents

### `GET /payouts`
Historique de tous les ramassages reçus par l'utilisateur.
- **Réponse (200)** : Liste des montants, dates et `blockchain_tx_hash`.

### `GET /payouts/{payout_id}`
Détails d'un ramassage spécifique (inclut le lien blockchain).

### `GET /incidents`
Liste des incidents enregistrés (retards de paiement).
- **Réponse (200)** : Utile pour afficher des alertes ou des malus à l'utilisateur.
