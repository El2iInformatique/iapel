# IApel — Gestion de Documents d'Intervention

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-blue.svg) ![Laravel 11](https://img.shields.io/badge/Laravel-11-red.svg) ![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)

IApel est une plateforme web développée pour **APEL Bâtiment**, permettant la dématérialisation et la gestion sécurisée des documents d'intervention (BI), des devis et des signatures électroniques.

## Sommaire
- [Aperçu](#aperçu)
- [Fonctionnalités clés](#fonctionnalités-clés)
- [Tech & prérequis](#tech--prérequis)
- [Installation rapide (Windows)](#installation-rapide-windows)
- [Utilisation & API](#utilisation--api)
- [Documentation Technique](#documentation-technique)
- [Licence](#licence)

## Aperçu
L'application sert de pont entre les techniciens sur le terrain et les clients finaux. Elle permet de générer des rapports d'intervention au format PDF, de faire signer des devis à distance via des liens sécurisés par jetons (tokens), et de gérer le suivi des documents.

## Fonctionnalités clés
- **Bons d'Intervention (BI)** : Saisie de formulaires en ligne et génération instantanée de rapports PDF.
- **Signature Électronique** : Validation et certification de devis par les clients via une interface dédiée.
- **Gestion par Tokens** : Accès sécurisé et temporaire aux documents sans nécessité de compte client complet.
- **Gestion de Photos** : Upload et compression de visuels liés aux interventions.
- **Outils d'Administration** : Scripts utilitaires (Python/C) pour la gestion des clients et des layouts.
- **Révision Flora** : Module d'identification et de gestion de données botaniques (groupes de plantes).

## Tech & prérequis
- **Backend** : PHP 8.2+ / Laravel 11
- **Frontend** : Blade Templates, Tailwind CSS, Vite
- **Base de données** : SQLite (dev), MySQL/PostgreSQL (prod)
- **PDF** : Intégration FPDI / TCPDF pour la manipulation et la certification.
- **Documentation** : Doxygen pour la génération de la doc technique.

## Installation rapide (Windows)
1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/El2iInformatique/iapel.git
   cd iapel
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Installer les dépendances Node**
   ```bash
   npm install
   ```

5. **Initialiser la base de données** (SQLite par défaut)
   ```bash
   php artisan migrate --seed
   ```

6. **Lier le stockage** (Crucial pour les PDFs et images)
   ```bash
   php artisan storage:link
   ```

7. **Lancer le serveur**
   ```bash
   npm run dev    # Front-end (Vite)
   php artisan serve
   ```

## Utilisation & API
L'application expose plusieurs points d'entrée pour l'intégration avec des outils tiers :
- `POST /api/generate-token` : Génère un jeton d'accès sécurisé.
- `GET /bi/{token}` : Affiche le formulaire d'intervention pour un technicien.
- `GET /signature/{token}` : Interface de signature pour le client.
- `GET /pdf/{token}` : Récupération du rapport final.

## Documentation Technique
Le projet utilise **Doxygen** pour maintenir une documentation à jour du code source.
Pour générer ou consulter la doc :
1. Assurez-vous d'avoir Doxygen installé.
2. Utilisez le fichier `Doxyfile` à la racine.
3. Les thèmes *doxygen-awesome* sont inclus pour un rendu moderne.

## Commandes utiles
- **Vider les caches** : `php artisan optimize:clear`
- **Tests** : `php artisan test`
- **Rebuild Front** : `npm run build`

## Base de données
- Développement : `database/database.sqlite`
- Production : Configurez vos accès `DB_*` dans le fichier `.env`.

## Crédits
Développé par **El2i Informatique** pour le projet **APEL Bâtiment**.
Site web : [https://www.el2i.fr](https://www.el2i.fr)
