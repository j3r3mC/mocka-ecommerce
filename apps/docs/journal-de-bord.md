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

### À faire

- Vérifier l'unicité de l'email
- Vérifier le hash du mot de passe
- Implémenter Login
- Implémenter Logout
- Implémenter Me
