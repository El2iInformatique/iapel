# IApel

Application web principalement en PHP avec des vues Blade — très probablement un projet Laravel — destinée à être déployée dans un environnement serveur classique (PHP + base de données). Ce dépôt contient majoritairement du code PHP et Blade, avec un peu de JavaScript/CSS pour l’interface, et quelques scripts Python éventuels d’outillage.

- Dépôt: [El2iInformatique/iapel](https://github.com/El2iInformatique/iapel)
- Branche par défaut: `main`
- Langages détectés:
  - PHP (code serveur, logique métier, Artisan, migrations…)
  - Blade (templates côté serveur)
  - JavaScript et CSS (front minimal)
  - Python (scripts d’outillage éventuels)

> Remarque: Cette documentation est structurée pour un projet Laravel standard. Adaptez les sections marquées “optionnel/si présent” selon le contenu exact du dépôt (services, policies, jobs, etc.).

---

## Sommaire

- [Aperçu](#aperçu)
- [Architecture et organisation du code](#architecture-et-organisation-du-code)
- [Prérequis](#prérequis)
- [Installation et démarrage rapide](#installation-et-démarrage-rapide)
- [Configuration (.env)](#configuration-env)
- [Bases de données et migrations](#bases-de-données-et-migrations)
- [Développement](#développement)
- [Tests](#tests)
- [Qualité, style et sécurité](#qualité-style-et-sécurité)
- [Build front (optionnel)](#build-front-optionnel)
- [Tâches planifiées et files (optionnel)](#tâches-planifiées-et-files-optionnel)
- [Journalisation et observabilité](#journalisation-et-observabilité)
- [Performance et cache](#performance-et-cache)
- [Déploiement](#déploiement)
- [Dépannage (FAQ)](#dépannage-faq)
- [Contribuer](#contribuer)
- [Licence](#licence)
- [Crédits](#crédits)

---

## Aperçu

iapel est une application web écrite principalement en PHP, avec des templates Blade et un front léger. L’empilement technique est cohérent avec un framework Laravel moderne:

- Contrôleurs HTTP et/ou API
- Modèles Eloquent (ORM)
- Vues Blade (`resources/views`)
- Routage dans `routes/web.php` (et éventuellement `routes/api.php`)
- Configuration centralisée dans `config/`
- Migrations et seeding dans `database/`

Architecture logique (schéma simplifié):

```
Client (navigateur)
   |
   v
Routes HTTP (web/api) --> Middleware --> Contrôleurs
                                          | 
                                          v
                                   Services (optionnel)
                                          |
                                          v
                                       Modèles (Eloquent) <--> Base de données
                                          |
                                          v
                                      Vues Blade (si web)
```

---

## Architecture et organisation du code

Arborescence Laravel typique (adaptée au projet):

```
app/
  Http/
    Controllers/         # Contrôleurs (web/API)
    Middleware/          # Middleware HTTP
    Requests/            # Form Requests (validation)
  Models/                # Modèles Eloquent
  Policies/              # Politiques d'autorisation (optionnel)
  Services/              # Services applicatifs (optionnel)
  Jobs/                  # Tâches asynchrones (optionnel)
  Events/, Listeners/    # Événements/Déclencheurs (optionnel)

bootstrap/               # Initialisation du framework
config/                  # Fichiers de configuration
database/
  migrations/            # Migrations
  seeders/               # Seeders
  factories/             # Factories pour tests/seeding
public/                  # Document root serveur web (assets, index.php)
resources/
  views/                 # Templates Blade
  js/, css/              # Front-end (si Vite/Laravel Mix)
routes/
  web.php                # Routes web
  api.php                # Routes API (optionnel)
storage/
  app/, logs/, framework/ # Fichiers générés et logs
tests/                   # Tests (Feature/Unit)
artisan                  # CLI Laravel
composer.json            # Dépendances PHP
package.json             # Dépendances front (si présent)
```

Principes clés:
- Validation via Form Requests dans `app/Http/Requests`.
- Autorisation via Policies/Gates dans `app/Policies`/`AuthServiceProvider`.
- Requêtes BD via Eloquent (relations, scopes).
- Séparation de la logique métier dans `app/Services` (si présente) pour garder des contrôleurs fins.
- Tâches asynchrones via `Jobs` et `Queues` (si présent).

---

## Prérequis

- PHP (version selon `composer.json`; pour les versions Laravel récentes, PHP >= 8.1/8.2)
- Composer
- Base de données: MySQL/MariaDB ou PostgreSQL
- Node.js + npm/yarn (si build front)
- Redis (optionnel: sessions, cache, queues)
- Git

---

## Installation et démarrage rapide

1) Cloner le dépôt
```bash
git clone https://github.com/El2iInformatique/iapel.git
cd iapel
```

2) Dépendances PHP
```bash
composer install
```

3) Variables d’environnement
```bash
cp .env.example .env
php artisan key:generate
```

4) Configurer la base de données dans `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iapel
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

5) Migrations + Seeders (si disponibles)
```bash
php artisan migrate --seed
```

6) Lier le stockage (pour les uploads)
```bash
php artisan storage:link
```

7) (Optionnel) Dépendances front + build
```bash
npm install
npm run build   # ou: npm run dev
```

8) Lancer le serveur de dev
```bash
php artisan serve
# → http://127.0.0.1:8000
```

---

## Configuration (.env)

Variables communes (adaptez selon vos besoins):

```
APP_NAME=iapel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iapel
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

- Si stockage S3: configurez `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`.
- Si queues asynchrones: `QUEUE_CONNECTION=redis` et Redis configuré.
- Si vous exposez une API: ajoutez les clés d’authentification (JWT, Sanctum/Passport, etc.).

---

## Bases de données et migrations

- Créez la base.
- Exécutez les migrations: `php artisan migrate`.
- Seeders de données de démo: `php artisan db:seed`.
- Réinitialiser en dev: `php artisan migrate:fresh --seed`.

Conventions:
- Clés primaires auto-incrémentées, timestamps `created_at/updated_at`.
- Contrainte d’intégrité via migrations (foreign keys), index sur colonnes fréquemment filtrées.

---

## Développement

Routage:
- Web: `routes/web.php`
- API (si présent): `routes/api.php` (pensez aux middlewares `auth:sanctum` ou `throttle`)

Contrôleurs:
- Regroupez les actions CRUD par ressource.
- Utilisez l’injection de dépendance et le route-model binding.

Validation:
- Créez des Form Requests dans `app/Http/Requests` pour centraliser les règles.
- Normalisez les messages d’erreur (localisation `resources/lang` si nécessaire).

Vues:
- Blade dans `resources/views`.
- Layouts partagés (`layouts/app.blade.php`), composants Blade pour réutilisables.

Services (optionnel):
- Encapsulez les règles métier complexes dans `app/Services`.

---

## Tests

- Cadre de test: PHPUnit (par défaut Laravel). Si Pest est utilisé, adaptez les commandes.
- Lancer tous les tests:
```bash
php artisan test
# ou
./vendor/bin/phpunit
```
- Tests de base de données avec transactions ou en mémoire (SQLite) pour la rapidité.
- Factories et seeders pour préparer les données.

---

## Qualité, style et sécurité

- Style PHP: PSR-12
- Outils (optionnels selon le projet):
  - Laravel Pint ou PHP-CS-Fixer: formatage
  - PHPStan/Psalm: analyse statique
  - Rector: refactoring
- Sécurité:
  - CSRF pour formulaires web (middleware par défaut).
  - XSS: échapper les données dans Blade (`{{ }}`).
  - Validation stricte des entrées (Form Requests).
  - Rôles/permissions via Policies ou packages dédiés (si présent).
  - Entêtes de sécurité (via config serveur et middleware).
- Secrets:
  - Jamais commiter de secrets. Utilisez `.env` et un gestionnaire de secrets en prod.

---

## Build front (optionnel)

Si le projet utilise Vite/Laravel Mix:
- Dev:
```bash
npm run dev
```
- Production:
```bash
npm run build
```
- Référence des assets dans Blade via les helpers Vite/Laravel (`@vite`, `mix()`).

---

## Tâches planifiées et files (optionnel)

- Cron Laravel:
  - Créez une tâche cron pour exécuter `php artisan schedule:run` chaque minute.
- Queues:
  - Démarrez un worker: `php artisan queue:work`
  - Superviser en prod via Supervisor ou systemd.

---

## Journalisation et observabilité

- Logs dans `storage/logs/laravel.log` (ou via `stack` vers syslog/Cloud).
- Niveaux de log par environnement (DEBUG en local, INFO/WARNING/ERROR en prod).
- Observabilité (optionnel):
  - Sentry/Bugsnag pour les erreurs
  - Laravel Telescope pour le dev (à restreindre hors local)

---

## Performance et cache

- Cache de configuration:
```bash
php artisan config:cache
```
- Cache des routes:
```bash
php artisan route:cache
```
- Cache des vues:
```bash
php artisan view:cache
```
- OPcache activé en production.
- Utilisez Redis pour sessions/cache si charge élevée.

---

## Déploiement

Check-list production:
1) Synchroniser le code (git pull/CI CD)
2) Dépendances PHP:
```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```
3) Build front (si nécessaire):
```bash
npm ci
npm run build
```
4) Migrations:
```bash
php artisan migrate --force
```
5) Caches:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
6) Lier le stockage:
```bash
php artisan storage:link
```
7) Droits d’accès:
- Le user du serveur web doit pouvoir écrire dans `storage/` et `bootstrap/cache/`.

8) Superviser:
- `queue:work`, `schedule:run`, monitoring de la santé (HTTP 200/5xx, temps de réponse, erreurs).

---

## Dépannage (FAQ)

- Erreur “No application encryption key has been specified”:
  - Exécutez `php artisan key:generate` et vérifiez `APP_KEY` dans `.env`.
- 500 en prod mais pas en local:
  - Vérifiez `APP_DEBUG=false`, les logs dans `storage/logs`, les permissions et `config:cache`.
- Problèmes de cache après déploiement:
  - `php artisan optimize:clear` puis régénérez les caches.
- Assets qui ne se chargent pas:
  - Vérifiez le build front, la publication des assets et `APP_URL`.
- Migrations échouent:
  - Vérifiez l’utilisateur/les droits DB et la version du SGBD.

---

## Crédits

- Propriétaire: [El2iInformatique](https://github.com/El2iInformatique)
- Projet: iapel
