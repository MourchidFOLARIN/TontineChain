# Description professionnelle du backend TontineChain

## 1. Présentation générale

Le backend de **TontineChain** est une API REST développée avec **Laravel 12** et **PHP 8.2+**. Il constitue le coeur technique de la plateforme et assure l’orchestration des fonctionnalités principales liées à la gestion numérique des tontines.

Il permet de gérer l’authentification des utilisateurs, la création des groupes, l’invitation des membres, le suivi des cotisations, les ramassages, les notifications, les votes, les enchères, la messagerie de groupe et les intégrations externes comme les paiements Mobile Money, les emails et la blockchain.

L’API est versionnée sous le préfixe:

```text
/api/v1
```

Elle expose actuellement **46 routes API**, dont la majorité est protégée par **Laravel Sanctum**.

## 2. Rôle du backend

Le backend joue le rôle de moteur central entre le frontend, la base de données et les services externes. Il garantit que chaque action importante de la tontine est traitée de manière structurée, sécurisée et traçable.

Ses responsabilités principales sont:

- authentifier les utilisateurs par OTP;
- gérer les profils et les scores de confiance;
- créer et administrer les groupes de tontine;
- suivre les cotisations des membres;
- déclencher les ramassages lorsque les conditions sont remplies;
- envoyer les notifications importantes;
- gérer les votes, les incidents et les enchères;
- produire les contrats PDF;
- documenter l’API via Swagger;
- connecter la plateforme aux services de paiement, de notification et de blockchain.

## 3. Architecture technique

Le projet suit une architecture Laravel claire et modulaire. Les responsabilités sont séparées entre les routes, les contrôleurs, les modèles, les services, les migrations et les tests.

### Routes API

Les routes principales sont définies dans:

```text
routes/api.php
```

Elles sont organisées sous `/api/v1` et regroupent les endpoints publics, les endpoints protégés par authentification et les webhooks.

### Contrôleurs

Les contrôleurs traitent les requêtes HTTP et appliquent la logique métier de premier niveau.

Contrôleurs principaux:

- `OtpController`
- `UserController`
- `GroupController`
- `ContributionController`
- `PayoutController`
- `WebhookController`
- `VoteController`
- `BiddingController`
- `MessageController`
- `IncidentController`
- `TontineNotificationController`
- `AiController`

### Modèles Eloquent

Les modèles représentent les entités métier de l’application.

Modèles principaux:

- `User`
- `Group`
- `GroupMember`
- `Contribution`
- `Payout`
- `Otp`
- `Vote`
- `VoteRecord`
- `Bid`
- `Message`
- `Incident`
- `TontineNotification`

### Services applicatifs

Les services isolent les traitements spécialisés et les intégrations externes.

Services présents:

- `PaymentService`
- `BlockchainService`
- `NotificationService`
- `SmsService`
- `TelegramService`
- `RiskAnalysisService`
- `KycService`
- `PayoutService`

Cette organisation rend le backend plus maintenable, plus évolutif et plus simple à tester.

## 4. Fonctionnalités déjà implémentées

### Authentification OTP

Le backend utilise une authentification sans mot de passe. L’utilisateur demande un code OTP par email, puis valide ce code pour obtenir un token API.

Fonctionnalités disponibles:

- demande de code OTP;
- stockage sécurisé du code sous forme de hash;
- expiration du code;
- limitation des tentatives;
- vérification du code;
- création automatique du compte si nécessaire;
- génération d’un token Bearer via Laravel Sanctum.

### Gestion des utilisateurs

Le module utilisateur permet de gérer le profil et les informations utiles à la participation dans les tontines.

Fonctionnalités disponibles:

- consultation du profil connecté;
- mise à jour du profil;
- consultation du score de confiance;
- consultation du solde;
- consultation des payouts reçus;
- leaderboard public.

### Gestion des groupes de tontine

Le backend permet de créer et administrer des groupes de tontine.

Fonctionnalités disponibles:

- création d’un groupe;
- définition du montant de cotisation;
- choix de la fréquence;
- choix de la méthode de ramassage;
- invitation de membres;
- adhésion au groupe;
- démarrage officiel de la tontine;
- statistiques du groupe;
- génération du contrat PDF;
- téléchargement du contrat.

Les méthodes de ramassage prévues sont:

- `sequential`: ramassage par ordre de position;
- `random`: ordre mélangé au démarrage du groupe;
- `bidding`: ramassage basé sur les enchères.

### Cotisations et paiements

Le backend gère les cotisations des membres et l’initialisation des paiements.

Fonctionnalités disponibles:

- consultation des cotisations en attente;
- initialisation d’un paiement;
- enregistrement des transactions;
- réception de webhook FedaPay;
- confirmation automatique des paiements;
- vérification du cycle complet avant déclenchement du payout.

### Webhook FedaPay

Le webhook FedaPay permet au backend de recevoir les confirmations de paiement.

Fonctionnalités disponibles:

- réception de l’événement `transaction.approved`;
- recherche de la contribution concernée;
- mise à jour du statut de paiement;
- envoi de notifications;
- déclenchement de la vérification du ramassage;
- contrôle de signature webhook en production.

### Payouts et ramassages

Le backend crée un payout lorsque toutes les cotisations d’un cycle sont confirmées.

Fonctionnalités disponibles:

- calcul du montant total du cycle;
- prise en compte du fonds de garantie;
- sélection du bénéficiaire en mode séquentiel;
- création de l’enregistrement payout;
- marquage du membre comme ayant reçu son ramassage;
- notification du bénéficiaire;
- passage au cycle suivant;
- clôture du groupe à la fin de la tontine.

### Enchères

Le module d’enchères permet à un membre de proposer une décote pour recevoir le ramassage plus tôt.

Fonctionnalités disponibles:

- soumission d’une enchère;
- mise à jour d’une enchère existante;
- contrôle que le membre appartient au groupe;
- contrôle que le groupe est en mode `bidding`;
- consultation des offres du cycle courant;
- tri des offres par montant de décote.

### Votes et gouvernance

Le backend inclut un système de votes pour gérer certaines décisions collectives.

Fonctionnalités disponibles:

- liste des votes;
- détail d’un vote;
- vote d’un membre;
- proposition d’échange de position.

### Messagerie de groupe

Une messagerie interne est disponible pour les groupes.

Fonctionnalités disponibles:

- consultation des messages;
- envoi de messages;
- messages système lors des événements importants;
- suppression de la messagerie après clôture du groupe.

### Notifications

Le backend gère plusieurs types de notifications.

Canaux prévus ou utilisés:

- notifications in-app;
- emails transactionnels;
- alertes Telegram;
- SMS selon configuration.

Fonctionnalités disponibles:

- liste des notifications;
- marquage comme lu;
- notification de paiement;
- notification de contrat;
- notification de ramassage;
- notification de fin de tontine.

### Contrats PDF

Le backend génère un contrat PDF pour les groupes de tontine.

Fonctionnalités disponibles:

- génération du PDF au démarrage du groupe;
- envoi du contrat par email;
- téléchargement du contrat via API.

### Blockchain

Le backend contient une couche de service dédiée à la blockchain.

Fonctionnalités disponibles:

- déploiement ou simulation du contrat de tontine;
- stockage de l’adresse du contrat;
- stockage du hash de transaction;
- appel de libération du payout.

### Documentation Swagger

Le projet intègre **L5-Swagger** pour documenter les endpoints.

Documentation accessible via:

```text
/api/documentation
/api/v1/docs
```

## 5. Sécurité

Le backend intègre plusieurs mécanismes de sécurité importants.

Mesures présentes:

- authentification par token Sanctum;
- routes sensibles protégées par `auth:sanctum`;
- validation des données entrantes;
- OTP à usage unique;
- expiration des OTP;
- limitation des tentatives OTP;
- réponses JSON pour les accès non authentifiés;
- vérification de signature webhook FedaPay en production;
- séparation des variables sensibles dans `.env`.

## 6. Base de données

La base de données est structurée autour des entités principales de la tontine:

- utilisateurs;
- groupes;
- membres de groupes;
- cotisations;
- payouts;
- OTP;
- incidents;
- notifications;
- votes;
- votes enregistrés;
- enchères;
- messages;
- tokens d’accès.

Des migrations récentes ont été ajoutées pour renforcer la cohérence du schéma, notamment sur l’unicité des emails, l’unicité du NPI hash, le champ `amount_fcfa` des payouts et la compatibilité du champ `metadata` des notifications.

## 7. Tests

Le projet contient des tests unitaires et fonctionnels.

Flux déjà couverts:

- demande d’OTP;
- vérification d’OTP;
- rejet d’un OTP invalide;
- création de groupe;
- adhésion à un groupe invité;
- initialisation d’un paiement.

État observé:

- la majorité des tests passe;
- un test local échoue actuellement à cause d’un problème de permission sur les fichiers de log, pas à cause d’une erreur métier confirmée.

## 8. État actuel du backend

Le backend est dans un état avancé. Les principales briques techniques et métier sont présentes. L’API démarre correctement, les routes sont enregistrées, les contrôleurs sont structurés et les modules essentiels sont déjà opérationnels.

Niveau actuel estimé:

```text
MVP avancé / pré-production
```

Ce qui est déjà solide:

- architecture Laravel propre;
- routes API versionnées;
- authentification OTP;
- protection Sanctum;
- gestion des groupes;
- gestion des cotisations;
- webhook FedaPay structuré;
- payouts automatiques en mode séquentiel;
- notifications;
- messagerie;
- votes;
- enchères côté API;
- documentation Swagger;
- tests fonctionnels de base.

## 9. Points à finaliser

Quelques éléments doivent encore être finalisés pour considérer le backend comme totalement prêt pour une production stricte.

À faire:

- appliquer les migrations en attente sur l’environnement local;
- corriger les permissions sur `storage/logs` et `.phpunit.result.cache`;
- finaliser la sélection du gagnant en mode `bidding` dans la logique de payout;
- ajouter des tests dédiés au webhook FedaPay;
- tester le cycle complet contribution -> webhook -> payout;
- vérifier les variables d’environnement de production;
- confirmer la configuration réelle SMTP, FedaPay, Telegram, SMS et blockchain;
- renforcer les tests de sécurité et de validation.

## 10. Résumé professionnel

Le backend TontineChain est une API Laravel modulaire, sécurisée et orientée services. Il couvre les besoins essentiels d’une plateforme de tontine numérique: authentification, gestion des groupes, cotisations, ramassages, gouvernance, notifications, contrats PDF, score de confiance et intégration avec des services externes.

Le projet possède déjà une base technique solide et une couverture fonctionnelle importante. Il peut être considéré comme un **MVP backend avancé**, proche d’un niveau production, avec quelques ajustements restants sur les migrations, les permissions locales, les tests avancés et la finalisation du mode enchères dans le processus de payout.
