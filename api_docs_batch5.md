## 🗳️ 10. Votes & Échanges (Swaps)

### `POST /groups/{group_id}/propose-swap`
Proposer d'échanger son rang de passage avec un autre membre.
- **Paramètres (JSON)** : `target_user_id`, `reason`.

### `GET /votes`
Liste des votes en cours (ex: approbation d'un échange).

### `POST /votes/{vote_id}/cast`
Voter pour ou contre une proposition.
- **Paramètres (JSON)** : `choice` (yes/no).

---

## 🎭 11. Outils de Démonstration (Spécial Jury)
*À utiliser par le Front pour simuler des scénarios en direct.*

### `GET /debug/run-deadlines`
Force le backend à vérifier les retards. Déclenche les alertes e-mail et baisse de score si quelqu'un n'a pas payé.

### `GET /debug/force-incident/{contribution_id}`
Force un incident critique sur une cotisation.
- **Effet** : Baisse immédiate de **-20 points** de score et notification d'alerte à tout le groupe.

### `GET /debug/migrate`
Force la mise à jour de la base de données (en cas de colonne manquante).

---

## 📖 12. Documentation Interactive (Swagger)
Pour tester chaque endpoint en direct avec une interface visuelle :
👉 **URL** : `https://tonnine-benin-backend.onrender.com/api/v1/docs`

---

**C'est tout ! Ton équipe Front a maintenant tout le nécessaire pour construire une application mobile incroyable.** 🚀🇧🇯🏆🥇✨🏅🏎️💎🔍
