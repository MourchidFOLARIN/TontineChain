## ⚙️ 13. Spécifications Techniques & Enums

Pour que le Front puisse coder les formulaires et les affichages sans erreur, voici les valeurs autorisées (Enums).

### 🌍 Langues (`preferred_language`)
- `fr` : Français
- `fon` : Fon
- `yor` : Yoruba

### 📅 Fréquence des Tontines (`frequency`)
- `daily` : Quotidien
- `weekly` : Hebdomadaire
- `monthly` : Mensuel

### 🏦 Méthodes de Ramassage (`payout_method`)
- `sequential` : L'ordre est défini au hasard au démarrage du groupe.
- `bidding` : Système d'enchères à chaque cycle.

### 🚩 Statuts
- **Groupes** : `pending` (en attente), `active` (en cours), `completed` (terminé).
- **Cotisations** : `pending` (à payer), `processing` (en cours sur FedaPay), `confirmed` (payé), `late` (en retard).
- **KYC** : `none`, `pending`, `verified`, `rejected`.

---

## 📜 14. Certificat de Fiabilité (Le bonus "Wow")

### `GET /users/me/certificate`
Génère les données pour un certificat de crédit basé sur le comportement de l'utilisateur.
- **Réponse (200)** :
  ```json
  {
    "title": "Certificat de Crédit TontineChain",
    "user": { "name": "...", "npi_hash": "...", "profession": "..." },
    "performance": {
      "score_confiance": 95,
      "total_contributions_validated": 12,
      "reliability_label": "Excellente"
    },
    "verification_link": "https://...",
    "demo_notice": { "message": "Simulation pour institutions financières..." }
  }
  ```

---

## 🏗️ 15. Structure de l'Objet "Groupe"
Voici les champs importants que le Front recevra dans `GET /groups/{id}` :
- `id` (UUID)
- `contract_address` : Adresse du contrat sur la Blockchain Polygon.
- `contract_tx_hash` : Lien de preuve de déploiement.
- `insurance_fund` : Montant disponible dans le fonds d'assurance du groupe.
- `next_due_date` : Date limite du prochain paiement.
- `members` : Liste des objets membres avec leur `rank` (ordre de passage).

---

**CONSEIL FINAL POUR LE FRONT** : 
Le backend renvoie toujours un objet `demo_notice` dans les réponses critiques. **Affichez-le dans une petite bannière ou un Toast** pendant la démo au jury pour souligner l'aspect technologique (Blockchain, IA, OCR).

**CETTE FOIS, C'EST 100% COMPLET !** 🏆🇧🇯🚀🥇✨
