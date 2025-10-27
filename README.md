<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Documentation Complète

## Introduction

Ce projet est une application Laravel conçue pour [insérer une description détaillée de l'objectif du projet]. Il utilise des technologies modernes telles que Laravel, TailwindCSS, et Vite pour offrir une expérience utilisateur optimale.

## Installation

### Prérequis

- PHP >= 8.1
- Composer
- Node.js >= 16
- SQLite (ou une autre base de données prise en charge par Laravel)

### Étapes d'installation

1. Clonez le dépôt :
   ```bash
   git clone <url-du-repo>
   ```
2. Accédez au dossier du projet :
   ```bash
   cd iapel
   ```
3. Installez les dépendances PHP :
   ```bash
   composer install
   ```
4. Installez les dépendances Node.js :
   ```bash
   npm install
   ```
5. Configurez le fichier `.env` :
   ```bash
   cp .env.example .env
   ```
   Modifiez les valeurs selon votre environnement.
6. Générez la clé de l'application :
   ```bash
   php artisan key:generate
   ```
7. Exécutez les migrations :
   ```bash
   php artisan migrate
   ```

## Configuration

- **Fichier `.env`** :
  - Configurez les paramètres de base de données.
  - Configurez les services tiers si nécessaire (ex. : Mail, Pusher, etc.).

## Utilisation

### Démarrer le serveur de développement

```bash
php artisan serve
```

### Compiler les assets

```bash
npm run dev
```

### Commandes artisan utiles

- `php artisan migrate` : Appliquer les migrations.
- `php artisan db:seed` : Peupler la base de données avec des données de test.
- `php artisan make:model` : Générer un modèle.

## Développement

### Structure du projet

- `app/` : Contient les modèles, contrôleurs, et middleware.
- `resources/views/` : Contient les vues Blade.
- `routes/` : Contient les fichiers de routes (`web.php`, `api.php`).
- `database/migrations/` : Contient les fichiers de migration.

### Ajouter une nouvelle fonctionnalité

1. Créez un contrôleur :
   ```bash
   php artisan make:controller NomDuControleur
   ```
2. Ajoutez les routes dans `routes/web.php` ou `routes/api.php`.
3. Créez les vues correspondantes dans `resources/views/`.

## Tests

### Exécuter les tests

```bash
php artisan test
```

### Ajouter un test

1. Créez un fichier de test :
   ```bash
   php artisan make:test NomDuTest
   ```
2. Implémentez les cas de test dans le fichier généré.

## Déploiement

### Étapes de déploiement

1. Configurez l'environnement de production dans `.env`.
2. Compilez les assets :
   ```bash
   npm run build
   ```
3. Exécutez les migrations :
   ```bash
   php artisan migrate --force
   ```
4. Configurez un serveur web (ex. : Nginx, Apache).

## FAQ

### Comment réinitialiser la base de données ?

```bash
php artisan migrate:fresh --seed
```

### Comment ajouter un package ?

1. Ajoutez le package avec Composer :
   ```bash
   composer require nom/package
   ```
2. Si nécessaire, publiez les fichiers de configuration :
   ```bash
   php artisan vendor:publish
   ```

### Où trouver les logs ?

Les logs se trouvent dans le dossier `storage/logs/`.

---

Pour toute question supplémentaire, veuillez consulter la [documentation officielle de Laravel](https://laravel.com/docs).
