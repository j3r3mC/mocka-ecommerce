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
