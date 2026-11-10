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

---

## Jour 4 - Module Profil

### Objectif

Mettre en place le module Profil de **Mocka-Ecommerce** afin de séparer :

```text
Compte utilisateur
→ identité privée et authentification

Profil
→ identité publique sur la marketplace
```

Le module doit permettre à un utilisateur authentifié de :

- créer son profil ;
- consulter son profil ;
- modifier ses informations publiques ;
- ajouter ou remplacer son avatar.

---

## Architecture du profil

Les informations liées à l’authentification restent stockées dans la table `users`.

```text
users
├── first_name
├── last_name
├── email
├── phone
└── password
```

Les informations publiques de la marketplace sont stockées dans une table dédiée :

```text
profiles
├── id
├── user_id
├── username
├── avatar_path
├── bio
├── city
├── created_at
└── updated_at
```

Cette séparation évite de mélanger les responsabilités liées à l’authentification avec celles liées à la présentation publique de l’utilisateur.

---

## Migration du profil

Création de la migration :

```text
database/migrations/2026_11_02_091345_create_profiles_table.php
```

Structure créée :

```php
Schema::create('profiles', function (Blueprint $table): void {
    $table->id();

    $table
        ->foreignId('user_id')
        ->unique()
        ->constrained()
        ->cascadeOnDelete();

    $table
        ->string('username', 50)
        ->unique();

    $table
        ->string('avatar_path')
        ->nullable();

    $table
        ->text('bio')
        ->nullable();

    $table
        ->string('city', 100)
        ->nullable();

    $table->timestamps();
});
```

### Contraintes appliquées

```text
✅ Un seul profil par utilisateur
✅ Username unique
✅ Relation vers users.id
✅ Suppression automatique du profil avec l’utilisateur
✅ Avatar facultatif
✅ Biographie facultative
✅ Ville facultative
```

---

## Modèle Profile

Création du modèle :

```text
app/Models/Profile.php
```

Champs autorisés en assignation de masse :

```text
user_id
username
avatar_path
bio
city
```

Relation ajoutée dans `Profile` :

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

Relation inverse ajoutée dans `User` :

```php
public function profile(): HasOne
{
    return $this->hasOne(Profile::class);
}
```

Relations disponibles :

```text
$user->profile
$profile->user
```

---

## Tests du modèle Profile

Création du fichier :

```text
tests/Feature/ProfileModelTest.php
```

Les tests vérifient :

- qu’un utilisateur peut avoir un profil ;
- que la relation `User → Profile` fonctionne ;
- que la relation `Profile → User` fonctionne ;
- que les informations du profil sont enregistrées en base ;
- qu’un utilisateur ne peut pas posséder plusieurs profils ;
- que le username doit être unique.

Résultat obtenu :

```text
3 tests réussis
5 assertions
```

---

## Ressource API

Création de :

```text
app/Http/Resources/ProfileResource.php
```

La ressource contrôle les données exposées par l’API :

```text
id
username
avatar_path
bio
city
```

Les informations internes suivantes ne sont pas exposées :

```text
user_id
created_at
updated_at
```

Cette couche permet de maîtriser le contrat JSON indépendamment du modèle Eloquent.

---

## Consultation du profil

### Endpoint

```http
GET /api/profile
```

### Comportements

```text
Utilisateur authentifié avec profil
→ 200 OK
→ données du profil

Utilisateur authentifié sans profil
→ 404 Not Found

Visiteur
→ 401 Unauthorized
```

### Réponse en cas de succès

```json
{
  "profile": {
    "id": 1,
    "username": "jeremy",
    "avatar_path": null,
    "bio": "Développeur web.",
    "city": "Béziers"
  }
}
```

### Réponse si le profil n’existe pas

```json
{
  "message": "Profil introuvable."
}
```

### Tests

Création de :

```text
tests/Feature/GetProfileTest.php
```

Scénarios couverts :

- récupération du profil par son propriétaire ;
- vérification des données retournées ;
- absence de `user_id` dans la réponse ;
- profil inexistant renvoyé en `404` ;
- visiteur refusé en `401`.

Résultat obtenu :

```text
3 tests réussis
7 assertions
```

---

## Création du profil

### Endpoint

```http
POST /api/profile
```

### Validation

Création de :

```text
app/Http/Requests/StoreProfileRequest.php
```

Règles appliquées au username :

```text
obligatoire
chaîne de caractères
minimum 3 caractères
maximum 50 caractères
lettres, chiffres, tiret et underscore uniquement
unique dans la table profiles
```

Règles appliquées aux autres champs :

```text
bio
→ facultative
→ chaîne de caractères
→ maximum 1 000 caractères

city
→ facultative
→ chaîne de caractères
→ maximum 100 caractères
```

### Comportements

```text
Utilisateur authentifié sans profil
→ 201 Created

Utilisateur possédant déjà un profil
→ 409 Conflict

Username déjà utilisé
→ 422 Unprocessable Entity

Username absent
→ 422 Unprocessable Entity

Visiteur
→ 401 Unauthorized
```

### Gestion du second profil

```json
{
  "message": "Un profil existe déjà pour cet utilisateur."
}
```

La limitation est appliquée à deux niveaux :

```text
Application
→ vérification avant la création

PostgreSQL
→ contrainte unique sur user_id
```

### Tests

Création de :

```text
tests/Feature/CreateProfileTest.php
```

Scénarios couverts :

- création d’un profil valide ;
- enregistrement en base ;
- refus d’un second profil ;
- refus d’un username déjà utilisé ;
- username obligatoire ;
- refus d’un visiteur.

Résultat obtenu :

```text
5 tests réussis
17 assertions
```

---

## Modification du profil

### Endpoint

```http
PATCH /api/profile
```

La mise à jour est partielle.

Un utilisateur peut envoyer uniquement les champs qu’il souhaite modifier :

```json
{
  "bio": "Nouvelle biographie."
}
```

Les autres informations restent inchangées.

### Validation

Création de :

```text
app/Http/Requests/UpdateProfileRequest.php
```

Les champs utilisent la règle :

```php
sometimes
```

Cela signifie qu’un champ est validé uniquement s’il est présent dans la requête.

Le username conserve sa contrainte d’unicité, mais le profil courant est ignoré lors de la vérification :

```php
Rule::unique('profiles', 'username')
    ->ignore($profile?->id)
```

Cette règle permet de conserver son username actuel sans recevoir une fausse erreur de doublon.

### Comportements

```text
Modification complète
→ 200 OK

Modification partielle
→ 200 OK

Username actuel conservé
→ accepté

Username utilisé par un autre profil
→ 422 Unprocessable Entity

Utilisateur sans profil
→ 404 Not Found

Visiteur
→ 401 Unauthorized
```

### Tests

Création de :

```text
tests/Feature/UpdateProfileTest.php
```

Scénarios couverts :

- modification complète du profil ;
- modification partielle ;
- conservation des champs non envoyés ;
- conservation du username actuel ;
- refus du username d’un autre profil ;
- gestion du profil inexistant ;
- refus d’un visiteur.

Résultat obtenu :

```text
6 tests réussis
18 assertions
```

---

## Gestion de l’avatar

### Endpoint

```http
POST /api/profile/avatar
```

L’avatar utilise un endpoint séparé afin de ne pas mélanger :

```text
Mise à jour des informations textuelles
et
Téléversement d’un fichier
```

### Validation

Création de :

```text
app/Http/Requests/UploadProfileAvatarRequest.php
```

Formats acceptés :

```text
JPEG
PNG
WebP
```

Limite appliquée :

```text
2 Mo maximum
```

Formats volontairement refusés :

```text
SVG
GIF
PDF
Autres fichiers
```

La validation vérifie que le fichier reçu est réellement une image.

---

## Stockage des avatars

Les avatars sont stockés sur le disque Laravel :

```text
public
```

Répertoire utilisé :

```text
avatars/
```

Le chemin relatif est enregistré dans :

```text
profiles.avatar_path
```

Lorsqu’un utilisateur remplace son avatar :

```text
1. Le nouvel avatar est stocké
2. avatar_path est mis à jour
3. L’ancien fichier est supprimé
```

Cet ordre évite de supprimer l’ancien avatar avant de savoir si le nouveau fichier a été enregistré correctement.

---

## Extension GD

Les tests utilisant de véritables images factices nécessitaient l’extension PHP GD.

Le conteneur PHP a été enrichi avec :

```text
libfreetype6-dev
libjpeg62-turbo-dev
libpng-dev
libwebp-dev
gd
```

Le Dockerfile prend désormais en charge :

```text
JPEG
PNG
WebP
FreeType
```

Vérification effectuée :

```bash
php -r "var_dump(extension_loaded('gd'));"
```

Résultat :

```text
bool(true)
```

Le conteneur `mocka-app` a été reconstruit sans supprimer les volumes PostgreSQL et Redis.

---

## Tests de l’avatar

Création de :

```text
tests/Feature/UploadProfileAvatarTest.php
```

Les tests utilisent :

```php
Storage::fake('public');
UploadedFile::fake()->image(...);
```

Cela permet de tester les fichiers sans écrire dans le stockage réel.

Scénarios couverts :

- téléversement d’un avatar valide ;
- mise à jour du chemin en base ;
- présence du nouveau fichier ;
- remplacement d’un avatar existant ;
- suppression de l’ancien fichier ;
- avatar obligatoire ;
- refus d’un fichier non-image ;
- refus d’une image supérieure à 2 Mo ;
- refus si le profil n’existe pas ;
- refus d’un visiteur.

Résultat obtenu :

```text
7 tests réussis
25 assertions
```

---

## Routes du module Profil

```http
GET   /api/profile
POST  /api/profile
PATCH /api/profile
POST  /api/profile/avatar
```

Toutes ces routes sont protégées par :

```php
auth:sanctum
```

---

## Architecture obtenue

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── ProfileController.php
│   ├── Requests/
│   │   ├── StoreProfileRequest.php
│   │   ├── UpdateProfileRequest.php
│   │   └── UploadProfileAvatarRequest.php
│   └── Resources/
│       └── ProfileResource.php
├── Models/
│   ├── Profile.php
│   └── User.php

database/
└── migrations/
    └── 2026_11_02_091345_create_profiles_table.php

tests/
└── Feature/
    ├── ProfileModelTest.php
    ├── GetProfileTest.php
    ├── CreateProfileTest.php
    ├── UpdateProfileTest.php
    └── UploadProfileAvatarTest.php
```

---

## Résumé des tests du module Profil

### Modèle et contraintes

```text
✅ Relations User ↔ Profile
✅ Persistance du profil
✅ Un seul profil par utilisateur
✅ Username unique
```

### Consultation

```text
✅ Consultation du profil
✅ Profil absent en 404
✅ Visiteur refusé en 401
✅ Données internes non exposées
```

### Création

```text
✅ Création d’un profil
✅ Données enregistrées en base
✅ Second profil refusé
✅ Username déjà utilisé refusé
✅ Username obligatoire
✅ Visiteur refusé
```

### Modification

```text
✅ Modification complète
✅ Modification partielle
✅ Conservation des champs absents
✅ Conservation du username actuel
✅ Username d’un autre profil refusé
✅ Profil absent en 404
✅ Visiteur refusé
```

### Avatar

```text
✅ Téléversement d’une image
✅ Enregistrement du chemin
✅ Remplacement de l’avatar
✅ Suppression de l’ancien fichier
✅ Validation du type
✅ Validation de la taille
✅ Profil absent en 404
✅ Visiteur refusé
```

---

## Résultat global des tests

La suite complète affiche :

```text
36 tests réussis
109 assertions
0 échec
Durée : 25,63 secondes
```

Les tests couvrent désormais :

```text
Module Auth
+
Module Profil
```

---

## Git

Le développement a été réalisé sur :

```text
feature/module-profil
```

Le module a ensuite été fusionné dans `develop` :

```text
Updating 24691fa..8476af4
Fast-forward
```

Le push de `develop` vers GitHub a réussi :

```text
24691fa..8476af4
develop → develop
```

État final :

```text
✅ Fusion sans conflit
✅ Fast-forward
✅ develop synchronisée avec origin/develop
✅ Module Profil intégré
```

---

## État d’avancement de la V1

Modules terminés :

```text
✅ Authentification
✅ Profils
```

Modules restant à développer :

```text
⬜ Produits
⬜ Images des produits
⬜ Favoris
⬜ Commandes
⬜ Paiements
⬜ Modération
```

Les images des produits pourront être intégrées comme un sous-module de Produits.

Il reste donc :

```text
5 grands modules métier
```

---

## Préparation du prochain module

Le prochain module sera :

```text
Produits
```

Branche prévue :

```text
feature/module-produits
```

Fonctionnalités prévues :

```text
Création d’une annonce
Consultation d’une annonce
Liste publique paginée
Liste des annonces personnelles
Modification par le propriétaire
Suppression par le propriétaire
Gestion des statuts
Gestion des images multiples
```

Estimation actuelle :

```text
Environ 9 à 14 heures
```

Cette estimation comprend le modèle, les endpoints, les autorisations, les images multiples et les tests.

---

## Résultat de fin de journée

Le module Profil V1 est entièrement opérationnel.

Le backend peut désormais :

```text
Créer un profil public
↓
Consulter le profil
↓
Modifier les informations publiques
↓
Téléverser un avatar
↓
Remplacer l’avatar
↓
Supprimer automatiquement l’ancien fichier
```

La journée se termine avec :

```text
✅ Authentification V1 terminée
✅ Profil V1 terminé
✅ 36 tests automatisés
✅ 109 assertions
✅ Aucun échec
✅ Environnement Docker compatible avec les images
✅ Branche Profile fusionnée dans develop
✅ Dépôt distant synchronisé
```

Le projet dispose maintenant d’une base utilisateur complète et testée. Le prochain développement commencera par le modèle et la migration du module Produits.

---

## Jour 5 - CRUD du module Produits

### Objectif

Construire le socle du module Produits de **Mocka-Ecommerce** avant d’intégrer la gestion des images.

Le périmètre retenu pour cette journée était le CRUD textuel complet :

```text
Créer un produit
Consulter un produit
Lister les produits publics
Lister ses propres produits
Modifier son produit
Supprimer logiquement son produit
```

La gestion des images, la conversion WebP et le serveur média seront développés dans une étape séparée.

---

## Préparation Git

Le développement du module a été isolé sur une branche dédiée :

```text
feature/module-produits
```

Cette branche a été créée depuis `develop` afin de préserver la stabilité du projet pendant le développement.

Commande utilisée :

```bash
git switch -c feature/module-produits
```

Puis publication de la branche distante :

```bash
git push -u origin feature/module-produits
```

---

## Architecture du produit

Création du modèle et de sa migration :

```bash
php artisan make:model Product -m
```

Fichiers générés :

```text
app/Models/Product.php
database/migrations/2026_11_09_131258_create_products_table.php
```

---

## Structure de la table products

La table `products` contient :

```text
products
├── id
├── user_id
├── title
├── description
├── price_cents
├── condition
├── status
├── city
├── published_at
├── created_at
├── updated_at
└── deleted_at
```

Migration mise en place :

```php
Schema::create('products', function (Blueprint $table): void {
    $table->id();

    $table
        ->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('title', 150);

    $table->text('description');

    $table->unsignedBigInteger('price_cents');

    $table->string('condition', 20);

    $table
        ->string('status', 20)
        ->default('draft');

    $table->string('city', 100);

    $table
        ->timestamp('published_at')
        ->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index([
        'status',
        'published_at',
    ]);

    $table->index([
        'user_id',
        'status',
    ]);
});
```

---

## Décisions liées au prix

Le prix est stocké en centimes :

```text
12,99 €  → 1299
129,99 € → 12999
```

Champ utilisé :

```php
price_cents
```

Ce choix évite les erreurs de précision liées aux nombres décimaux lors des calculs monétaires.

Les règles applicatives imposent également :

```text
price_cents doit être un entier
price_cents doit être supérieur ou égal à 1
```

---

## États physiques du produit

Création de l’enum :

```text
app/Enums/ProductCondition.php
```

Valeurs disponibles :

```php
enum ProductCondition: string
{
    case New = 'new';
    case LikeNew = 'like_new';
    case Good = 'good';
    case Fair = 'fair';
}
```

Correspondance métier :

```text
new       → neuf
like_new  → comme neuf
good      → bon état
fair      → état correct
```

---

## Statuts de l’annonce

Création de l’enum :

```text
app/Enums/ProductStatus.php
```

Valeurs disponibles :

```php
enum ProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Sold = 'sold';
    case Archived = 'archived';
}
```

Rôle des statuts :

```text
draft
→ brouillon privé

published
→ annonce publique et disponible

sold
→ annonce vendue, mais toujours visible publiquement

archived
→ annonce retirée de la liste publique
```

Par défaut, un produit est créé avec :

```text
status = draft
published_at = null
```

Le client ne peut donc pas publier directement une annonce lors de sa création.

---

## Date de publication

Le champ :

```php
published_at
```

est distinct de :

```php
created_at
```

Cette séparation permet de distinguer :

```text
created_at
→ date de création du brouillon

published_at
→ date réelle de publication de l’annonce
```

La liste publique utilise `published_at` pour trier les annonces.

---

## Modèle Product

Création et configuration de :

```text
app/Models/Product.php
```

Champs autorisés en assignation de masse :

```text
user_id
title
description
price_cents
condition
status
city
published_at
```

Casts configurés :

```php
protected function casts(): array
{
    return [
        'price_cents' => 'integer',
        'condition' => ProductCondition::class,
        'status' => ProductStatus::class,
        'published_at' => 'datetime',
    ];
}
```

Le modèle utilise également les suppressions logiques :

```php
use SoftDeletes;
```

---

## Relations Eloquent

Un utilisateur peut posséder plusieurs produits :

```php
public function products(): HasMany
{
    return $this->hasMany(Product::class);
}
```

Un produit appartient à un utilisateur :

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

Relations disponibles :

```php
$user->products;
$product->user;
```

---

## Suppression logique

La suppression d’un produit ne détruit pas immédiatement sa ligne en base.

La migration ajoute :

```php
$table->softDeletes();
```

Cela crée :

```text
deleted_at
```

Avant suppression :

```text
deleted_at = null
```

Après suppression :

```text
deleted_at = date et heure de suppression
```

La ligne reste alors présente dans PostgreSQL, mais elle est automatiquement exclue des requêtes Eloquent ordinaires.

Opérations disponibles :

```php
$product->delete();
$product->restore();
$product->forceDelete();
$product->trashed();
```

Requêtes spécifiques :

```php
Product::withTrashed()->get();
Product::onlyTrashed()->get();
```

Ce mécanisme fournit un filet de sécurité en cas de suppression accidentelle et prépare les futurs besoins de support, de modération et d’historique des commandes.

---

## Différence entre archivage et suppression

Les statuts métier et la suppression logique ont des responsabilités différentes.

### Produit archivé

```text
status = archived
deleted_at = null
```

Le produit existe normalement. Il reste visible par son propriétaire, mais n’apparaît plus dans la liste publique.

### Produit supprimé

```text
deleted_at != null
```

Le produit est considéré comme supprimé et n’apparaît plus dans les requêtes ordinaires, même pour son propriétaire.

---

## Tests du modèle Product

Création de :

```text
tests/Feature/ProductModelTest.php
```

Scénarios couverts :

- relation `User → Products` ;
- relation `Product → User` ;
- enregistrement du prix en centimes ;
- cast de `ProductCondition` ;
- cast de `ProductStatus` ;
- cast de `published_at` ;
- statut `draft` par défaut ;
- date de publication nulle par défaut ;
- suppression logique ;
- exclusion des produits supprimés des requêtes normales ;
- récupération avec `withTrashed()` ;
- restauration d’un produit.

Résultat obtenu :

```text
5 tests réussis
```

---

## Ressource API Product

Création de :

```text
app/Http/Resources/ProductResource.php
```

Données exposées :

```text
id
title
description
price_cents
condition
status
city
published_at
created_at
updated_at
```

Données internes non exposées :

```text
user_id
deleted_at
```

La ressource garantit une représentation JSON stable et contrôlée.

---

## Création d’un produit

### Endpoint

```http
POST /api/products
```

Cette route est protégée par :

```php
auth:sanctum
```

### Validation

Création de :

```text
app/Http/Requests/StoreProductRequest.php
```

Règles appliquées :

```text
title
→ obligatoire
→ chaîne
→ maximum 150 caractères

description
→ obligatoire
→ chaîne
→ maximum 5 000 caractères

price_cents
→ obligatoire
→ entier
→ minimum 1

condition
→ obligatoire
→ valeur de ProductCondition

city
→ obligatoire
→ chaîne
→ maximum 100 caractères
```

Les champs suivants ne sont pas acceptés depuis le client :

```text
user_id
status
published_at
deleted_at
```

Le propriétaire est déterminé depuis la session :

```php
$request
    ->user()
    ->products()
    ->create($request->validated());
```

Le produit est ensuite rechargé avec :

```php
$product->refresh();
```

Cette étape récupère notamment le statut `draft` appliqué par défaut par PostgreSQL avant la sérialisation de la réponse.

### Comportements validés

```text
Utilisateur authentifié + données valides
→ 201 Created

Visiteur
→ 401 Unauthorized

Données invalides
→ 422 Unprocessable Entity
```

### Protection du statut

Même si le client envoie :

```json
{
  "status": "published",
  "published_at": "2026-11-09 13:00:00"
}
```

le produit est créé avec :

```text
status = draft
published_at = null
```

### Tests

Création de :

```text
tests/Feature/CreateProductTest.php
```

Résultat obtenu :

```text
8 tests réussis
30 assertions
```

---

## Consultation d’un produit

### Endpoint

```http
GET /api/products/{product}
```

Cette route est publique.

### Règles de visibilité

```text
published
→ visible
```

---

# Jour 6: Mise en place de l’infrastructure Media

## Objectif de la session

L’objectif était de préparer une infrastructure dédiée au stockage des images avant d’implémenter les images multiples des produits.

L’architecture retenue devait respecter les principes suivants :

```text
Laravel est le seul service autorisé à écrire les fichiers
Nginx expose les médias publiquement en lecture seule
Les fichiers sont stockés dans un volume Docker persistant
Les médias ne dépendent pas du cycle de vie des conteneurs
Les images sont converties en WebP avant leur stockage définitif
```

La première utilisation concrète de cette infrastructure a été la migration des avatars de profil.

---

## Organisation des branches Git

Le CRUD textuel Produits avait initialement été développé sur :

```text
feature/module-produits
```

Le CRUD étant terminé et entièrement testé, le chantier Media a été isolé sur une branche dédiée :

```text
feature/media-storage
```

Cette séparation permet de distinguer clairement :

```text
feature/module-produits
→ logique métier textuelle des produits

feature/media-storage
→ infrastructure de stockage des images
→ serveur média
→ traitement WebP
→ migration des avatars

feature/product-images
→ futures images multiples des produits
```

Après validation, la branche Media a été fusionnée dans :

```text
develop
```

État final de l’historique :

```text
9015390 feat(media): migration des avatars vers WebP
cd5cd55 chore(deps): ajout du traitement d'images et correctifs de sécurité
08b77a7 NGINX + container laravel-media
4fa18bf feat(product): ajout du CRUD des produits
```

Une nouvelle branche a ensuite été créée pour la prochaine étape :

```text
feature/product-images
```

État Git en fin de journée :

```text
Branche actuelle : feature/product-images
Branche distante : origin/feature/product-images
Répertoire de travail : propre
```

---

# Architecture Media retenue

## Vue d’ensemble

L’architecture mise en place est la suivante :

```text
Application cliente
        ↓
API Laravel
        ↓
validation et traitement des images
        ↓
volume Docker mocka_media
        ↑
Nginx Media en lecture seule
        ↓
http://localhost:8081
```

Responsabilités des services :

```text
mocka-app
→ écrit, remplace et supprime les fichiers

mocka-media
→ lit et expose les fichiers

mocka-postgres
→ stocke uniquement les chemins relatifs

mocka-redis
→ cache et futures files de traitement
```

Les fichiers binaires ne sont pas enregistrés dans PostgreSQL.

La base contient uniquement des chemins relatifs, par exemple :

```text
avatars/user-1/uuid.webp
```

---

# Volume Docker persistant

## Modification de Docker Compose

Un volume Docker nommé a été ajouté :

```yaml
volumes:
  postgres_data:
  mocka_media:
```

Le service Laravel monte ce volume en écriture :

```yaml
services:
  app:
    volumes:
      - ./apps/api:/var/www
      - mocka_media:/var/www/storage/app/media
```

Le serveur Nginx monte le même volume en lecture seule :

```yaml
services:
  media:
    image: nginx:1.29-alpine
    container_name: mocka-media
    restart: unless-stopped

    ports:
      - "8081:80"

    volumes:
      - mocka_media:/usr/share/nginx/html:ro
      - ./docker/media/default.conf:/etc/nginx/conf.d/default.conf:ro
```

Le nom effectif du volume généré par Docker Compose est :

```text
mocka-ecommerce_mocka_media
```

---

## Docker Compose complet

La configuration finale comprend quatre services :

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile

    container_name: mocka-app
    working_dir: /var/www

    volumes:
      - ./apps/api:/var/www
      - mocka_media:/var/www/storage/app/media

    depends_on:
      - postgres
      - redis

    tty: true

  postgres:
    image: postgres:17
    container_name: mocka-postgres
    restart: unless-stopped

    environment:
      POSTGRES_DB: mocka
      POSTGRES_USER: mocka
      POSTGRES_PASSWORD: mocka

    ports:
      - "5432:5432"

    volumes:
      - postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:8-alpine
    container_name: mocka-redis
    restart: unless-stopped

    ports:
      - "6379:6379"

  media:
    image: nginx:1.29-alpine
    container_name: mocka-media
    restart: unless-stopped

    ports:
      - "8081:80"

    volumes:
      - mocka_media:/usr/share/nginx/html:ro
      - ./docker/media/default.conf:/etc/nginx/conf.d/default.conf:ro

volumes:
  postgres_data:
  mocka_media:
```

---

# Serveur Nginx Media

## Configuration

Création du fichier :

```text
docker/media/default.conf
```

Contenu :

```nginx
server {
    listen 80;
    server_name _;

    root /usr/share/nginx/html;

    location / {
        try_files $uri =404;

        add_header X-Content-Type-Options "nosniff" always;
        add_header Cache-Control "public, max-age=86400";
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

Le serveur Media remplit uniquement le rôle de serveur de fichiers statiques.

Il ne contient pas PHP et n’exécute aucun code applicatif.

---

## Validation de la configuration

La syntaxe Docker Compose a été vérifiée avec :

```powershell
docker compose config
```

Les services ont ensuite été démarrés :

```powershell
docker compose up -d --force-recreate app media
```

État obtenu :

```text
mocka-app        Up
mocka-media      Up   0.0.0.0:8081->80/tcp
mocka-postgres   Up   0.0.0.0:5432->5432/tcp
mocka-redis      Up   0.0.0.0:6379->6379/tcp
```

La configuration Nginx a également été validée :

```powershell
docker exec mocka-media nginx -t
```

Résultat :

```text
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

---

# Vérification du volume partagé

## Écriture depuis Laravel

Un fichier de contrôle a été créé depuis `mocka-app` :

```bash
mkdir -p storage/app/media/health
echo media-ok > storage/app/media/health/test.txt
```

Le fichier était visible depuis le serveur média :

```powershell
docker exec mocka-media cat /usr/share/nginx/html/health/test.txt
```

Résultat :

```text
media-ok
```

---

## Accès HTTP

Le même fichier a été récupéré par HTTP :

```powershell
(Invoke-WebRequest http://localhost:8081/health/test.txt).Content
```

Résultat :

```text
media-ok
```

Cette vérification confirme le chemin complet :

```text
mocka-app
→ volume mocka_media
→ mocka-media
→ port 8081
→ navigateur ou client HTTP
```

---

## Lecture
