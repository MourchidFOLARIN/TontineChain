# Corrections du Backend - Audit de Sécurité et Cohérence

Date: 12 mai 2026
Corrections appliquées le: 2026-05-12

## Migrations créées

### 1. `2026_05_12_000001_add_unique_email_to_users_table.php`
**Problème**: `users.email` n'avait pas de contrainte unique, risquant des doublons.
**Solution**: Ajouter `UNIQUE` sur la colonne `email`.
**Impact**: Prévient la création de plusieurs comptes avec la même adresse email.

### 2. `2026_05_12_000002_add_unique_npi_hash_to_users_table.php`
**Problème**: `users.npi_hash` n'avait pas d'index unique malgré des vérifications en code.
**Solution**: Ajouter un index unique sur `npi_hash`.
**Impact**: Garantit l'unicité du NPI au niveau base de données.

### 3. `2026_05_12_000003_add_amount_fcfa_to_payouts_table.php`
**Problème**: `WebhookController::checkAndReleasePayout()` tente d'écrire `amount_fcfa` mais la colonne n'existe pas.
**Solution**: Ajouter la colonne `amount_fcfa` (decimal 18,2) nullable aux payouts.
**Impact**: Permet la persistance correcte du montant net payé après déductions.

### 4. `2026_05_12_000004_fix_tontine_notifications_metadata_type.php`
**Problème**: `metadata` était défini en `jsonb` (PostgreSQL-only), causant des erreurs en MySQL/SQLite.
**Solution**: Adapter dynamiquement le type selon le driver DB (jsonb pour PG, json pour MySQL/SQLite).
**Impact**: Compatibilité avec tous les drivers de base de données.

### 5. `2026_05_12_000005_refactor_vote_records_model.php`
**Problème**: `VoteRecord` était imbriquée dans `Vote.php`, pas cohérente avec le reste du projet.
**Solution**: Créer un modèle séparé `app/Models/VoteRecord.php`.
**Impact**: Clarté architecturale et facilité de maintenance.

### 6. `2026_05_12_000006_audit_bidding_payout_method_implementation.php`
**Problème**: Mode `payout_method = 'bidding'` est défini en base mais pas implémenté en logique.
**Solution**: Migration d'audit documenting les points d'implémentation manquants.
**Impact**: À implémenter dans `WebhookController::checkAndReleasePayout()`.

---

## Modèles corrigés

### `app/Models/Group.php`
- ✅ `$fillable` déjà contenait `insurance_fund` et `insurance_percent`
- ✅ Ajout de `$casts` pour typer les décimales

### `app/Models/Payout.php`
- ✅ Ajout `amount_fcfa` à `$fillable` (manquait pour le webhook)

### `app/Models/Vote.php`
- ✅ Suppression de la classe `VoteRecord` imbriquée
- ✅ Import du modèle séparé `VoteRecord`

### `app/Models/VoteRecord.php` (NOUVEAU)
- ✅ Créé en tant que modèle séparé avec relations

---

## Recommandations d'implémentation

1. **Exécuter les migrations** : `php artisan migrate`

2. **Tester les contraintes** :
   ```bash
   # Vérifier unique email
   # Vérifier unique npi_hash
   ```

3. **Implémenter bidding** : Dans `WebhookController::checkAndReleasePayout()`:
   - Ajouter logique pour chercher le bid gagnant quand `payout_method = 'bidding'`
   - Appliquer la réduction du discount

4. **Valider les webhooks FedaPay** : Tester le flux de paiement complet

5. **Vérifier les types numériques** : S'assurer que `insurance_fund`, `amount_fcfa`, etc. sont bien castés

---

## Tests à effectuer

- [ ] Migration schema :  `php artisan migrate:status`
- [ ] Créer un utilisateur avec email unique
- [ ] Créer deux utilisateurs avec même email (doit échouer)
- [ ] Créer un utilisateur et ajouter NPI (doit être unique)
- [ ] Webhook FedaPay : vérifier `amount_fcfa` sauvegardé correctement
- [ ] Vérifier `metadata` JSON sauvegardé dans notifications
- [ ] Vérifier votes et votes_records créés correctement
