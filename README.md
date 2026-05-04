# 🔗 TontineChain - Plateforme Complète
**Inclusion Financière, IA & Blockchain au Bénin | Hackathon MIABE 2026**

## 🌐 Liens de Déploiement (Render)
- **Application Complète (Frontend + Backend)** : [https://tonnine-benin-backend.onrender.com](https://tonnine-benin-backend.onrender.com)
- **Documentation API** : [https://tonnine-benin-backend.onrender.com/api/documentation](https://tonnine-benin-backend.onrender.com/api/documentation)

---


![TontineChain Architecture](tontinechain_architecture_premium_1777719060614.png)

TontineChain Backend est le moteur central d'orchestration de la plateforme. Il gère l'authentification sécurisée, l'analyse prédictive des risques, l'interfaçage avec la blockchain Polygon et l'intégration des passerelles de paiement locales.

---

## 🏛️ Architecture Technique

Le backend est construit sur une architecture **Service-Oriented** (SOA) au-dessus de Laravel 12, garantissant une séparation stricte des préoccupations et une extensibilité maximale.

### 1. Couche de Services (Core Logic)
- **`RiskAnalysisService`** : Moteur d'IA (ML-based) qui évalue le score de confiance des membres et prédit les risques de défaut de paiement avant chaque cycle.
- **`BlockchainService`** : Gère les interactions avec les Smart Contracts sur **Polygon PoS**. Utilise des transactions signées via un relayer pour offrir une expérience sans frais de gaz (Gasless) aux utilisateurs.
- **`PaymentService`** : Intégration de l'API **FedaPay** pour la gestion des flux Mobile Money (MTN, Moov).
- **`NotificationService`** : Orchestre les alertes multi-canaux (SMS & WhatsApp via **Infobip**) pour les OTP et les rappels de cotisation.

### 2. Couche de Persistance
- **SQL** : SQLite (Développement) / PostgreSQL (Production) pour les données structurées.
- **Blockchain** : Stockage immuable des preuves de paiement et des états des cycles de tontine.

### 3. Sécurité & Authentification
- **Sanctum** : Gestion des tokens API pour le frontend.
- **OTP Login** : Authentification à deux facteurs native via SMS/WhatsApp pour une sécurité adaptée au contexte local.

---

## 🛠️ Stack Technologique

- **Framework** : Laravel 12 (PHP 8.2+)
- **Smart Contracts** : Solidity (Déployés sur Polygon)
- **Paiements** : FedaPay SDK
- **Communications** : Infobip API (WhatsApp Business & SMS)
- **Documentation** : Swagger / L5-Swagger

---

## 🚀 Installation & Configuration

### Pré-requis
- PHP 8.2+
- Composer
- SQLite (ou un serveur PostgreSQL)

### Étapes d'installation

1. **Cloner le projet** :
   ```bash
   git clone [url-du-repo]
   cd TonnineBenin
   ```

2. **Installer les dépendances** :
   ```bash
   composer install
   ```

3. **Configuration de l'environnement** :
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Note : Configurez vos clés Infobip, FedaPay et vos credentials Polygon dans le `.env`.*

4. **Migration et Seed** (Base de données) :
   ```bash
   php artisan migrate --seed
   ```

5. **Lancer le serveur de développement** :
   ```bash
   php artisan serve --port=8000
   ```

---

## 📖 Documentation API
La documentation complète des endpoints (Swagger) est accessible une fois le serveur lancé à l'adresse suivante :
`http://localhost:8000/api/documentation`

---

## 🤖 Analyse IA & Blockchain
Le système intègre nativement :
- **Proof of Payout** : Chaque ramassage est vérifié par un contrat intelligent.
- **Dynamic Trust Scoring** : Un algorithme qui ajuste le score de confiance des utilisateurs en temps réel selon leur comportement transactionnel.

---

## 📄 Licence
Projet développé dans le cadre du **Hackathon MIABE 2026**.
Domaine : D02 - Inclusion Financière.
Made with ❤️ by Mourchid AI.
