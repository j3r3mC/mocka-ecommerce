# Journal de bord

## Jour 1 - Mise en place du socle technique

### Objectif

Construire une base de développement propre, reproductible et sécurisée pour le projet **Mocka-Ecommerce** avant d'écrire la moindre fonctionnalité métier.

---

## Architecture retenue

### Backend

- Laravel 13.39
- PHP 8.4.27
- PostgreSQL 17
- Redis 8
- Laravel Sanctum

### Frontend

- Next.js (à venir)

### Déploiement

- Docker Compose
- Conteneurs dédiés

### Organisation du monorepo

```text
mocka-ecommerce/
├── apps/
│   └── api/
├── docs/
├── docker/
├── docker-compose.yml
└── README.md
```

---

## Git

### Réalisé

- Initialisation du dépôt Git
- Création de la branche `main`
- Création de la branche `develop`
- Configuration de GitHub via SSH
- Premier push du projet

### Convention retenue

```text
main    → stable
develop → développement
```

---

## Docker

### Réalisé

Création des services :

```text
mocka-app
mocka-postgres
mocka-redis
```

### Vérifications

- Docker Desktop opérationnel
- Docker Compose opérationnel
- Réseau Docker fonctionnel
- Communication entre conteneurs validée

---

## PHP

### Réalisé

Validation de l'environnement :

```text
PHP 8.4.27
Composer 2.10.3
```

### Motivation

Utiliser une version récente, stable et corrigée des vulnérabilités connues.

---

## Laravel

### Réalisé

Installation de :

```text
Laravel 13.39
```

dans :

```text
apps/api
```

### Vérification

```bash
php artisan --version
```

Résultat :

```text
Laravel Framework 13.39
```

---

## PostgreSQL

### Réalisé

Création du conteneur :

```text
mocka-postgres
```

Configuration :

```text
Base      : mocka
Utilisateur : mocka
Mot de passe : mocka
```

### Vérifications

Connexion Laravel ↔ PostgreSQL validée.

Migrations exécutées avec succès.

Tables créées :

```text
users
cache
jobs
migrations
```

---

## Redis

### Réalisé

Création du conteneur :

```text
mocka-redis
```

Configuration du cache Laravel sur Redis.

---

## Sanctum

### Réalisé

Installation :

```text
laravel/sanctum 4.3.3
```

Publication des ressources.

Création de la table :

```text
personal_access_tokens
```

Migration exécutée avec succès.

---

## Décisions d'architecture

### Séparation Front / Back

Décision :

```text
Laravel = API REST uniquement
Next.js = Frontend complet
```

---

### Sécurité

Principes retenus :

- Validation via FormRequest obligatoire
- Typage strict PHP
- Contraintes PostgreSQL
- Tests automatisés
- Sanctum pour l'authentification

---

### Simplicité

Choix assumés :

```text
✅ API REST classique
✅ Modular Monolith
✅ Laravel conventions

❌ DDD
❌ CQRS
❌ Microservices
❌ JWT
❌ OAuth
❌ 2FA pour le MVP
```

---

## Documentation

Création de :

```text
docs/
├── architecture.md
├── database.md
└── roadmap.md
```

---

## Roadmap validée

### MVP

- Auth
- Profiles
- Products
- Product Images
- Favorites
- Orders
- Payments
- Moderation

### V2

- Offers

### V3

- Bundles

### V4

- Messaging

### V5

- Auctions

---

## Résultat de fin de journée

```text
✅ Git configuré
✅ Docker configuré
✅ Laravel opérationnel
✅ PostgreSQL connecté
✅ Redis connecté
✅ Sanctum installé
✅ Architecture validée
✅ Environnement de développement prêt
```

---

## Jour 2 - Module Auth

### Réalisé

- Création du contrôleur AuthController
- Création du RegisterRequest
- Mise en place de la validation métier :
  - prénom
  - nom
  - email unique
  - téléphone
  - mot de passe
- Migration de la table users :
  - suppression du champ name
  - ajout de first_name
  - ajout de last_name
  - ajout de phone
- Adaptation du modèle User
- Création de la route POST /api/auth/register
- Mise en place des tests d'intégration
- Premier cycle TDD complet
- Création effective d'un utilisateur en base
- Test de création d'utilisateur validé

### Résultat

✅ Un utilisateur peut désormais s'inscrire via l'API.

## Jour 3 - Finalisation du module d’authentification

### Objectif

Finaliser le socle d’authentification de **Mocka-Ecommerce** en ajoutant :

- la connexion utilisateur ;
- la gestion des erreurs de connexion ;
- la déconnexion sécurisée ;
- la récupération de l’utilisateur courant ;
- les tests fonctionnels associés.

---

## Choix d’authentification

Le frontend de l’application sera développé avec Next.js et communiquera avec l’API Laravel.

Pour le client web officiel, la stratégie retenue est l’authentification SPA de Sanctum :

```text
Next.js
↓
Cookie de session HttpOnly
↓
Laravel Sanctum
↓
Session Laravel
```

## Résultats de la journée

Création d’un utilisateur
✅ Refus d’un email déjà utilisé
✅ Vérification du hachage du mot de passe
✅ Connexion avec des identifiants valides
✅ Refus d’un email inconnu
✅ Refus d’un mauvais mot de passe
✅ Validation des champs obligatoires
✅ Maintien de l’état authentifié
✅ Déconnexion d’un utilisateur connecté
✅ Refus du Logout pour un visiteur
✅ Récupération de l’utilisateur courant
✅ Refus de Me pour un visiteur
✅ Absence du mot de passe dans les réponses
✅ Absence du remember_token dans les réponses
✅ Absence de token personnel pour la session SPA

## Résumé des tests automatisés

La suite de tests du module Auth couvre les principaux parcours d’authentification et leurs cas d’échec.

### Inscription

Le fichier `RegisterUserTest.php` vérifie :

- qu’un utilisateur peut créer un compte avec des données valides ;
- que les informations sont enregistrées en base de données ;
- qu’une même adresse email ne peut pas être utilisée deux fois ;
- que le mot de passe n’est jamais stocké en clair ;
- que le mot de passe enregistré correspond bien au mot de passe fourni après vérification du hash.

### Connexion

Le fichier `LoginUserTest.php` vérifie :

- qu’un utilisateur peut se connecter avec des identifiants valides ;
- que le bon utilisateur est associé à la session ;
- que la réponse contient les informations de l’utilisateur connecté ;
- qu’aucun token personnel Sanctum n’est créé pour l’authentification SPA ;
- qu’un email inconnu renvoie une erreur `401 Unauthorized` ;
- qu’un mot de passe incorrect renvoie une erreur `401 Unauthorized` ;
- que le même message générique est utilisé pour les identifiants invalides ;
- que l’email est obligatoire ;
- que le mot de passe est obligatoire ;
- qu’une tentative échouée laisse le client non authentifié.

### Déconnexion

Le fichier `LogoutUserTest.php` vérifie :

- qu’un utilisateur authentifié peut se déconnecter ;
- que l’API renvoie un statut `200 OK` ;
- que le message de confirmation est correct ;
- que l’utilisateur n’est plus authentifié après la déconnexion ;
- qu’un visiteur ne peut pas appeler la route Logout ;
- qu’un visiteur reçoit une erreur `401 Unauthorized`.

### Utilisateur courant

Le fichier `CurrentUserTest.php` vérifie :

- qu’un utilisateur authentifié peut récupérer son identité ;
- que les données retournées correspondent au bon utilisateur ;
- que l’identifiant, le prénom, le nom, l’email et le téléphone sont présents ;
- que le mot de passe n’est jamais exposé ;
- que le `remember_token` n’est jamais exposé ;
- qu’un visiteur ne peut pas accéder à l’endpoint `Me` ;
- qu’un visiteur reçoit une erreur `401 Unauthorized`.

### Fonctionnement de la base de test

Les tests utilisent :

```php
use RefreshDatabase;
```
