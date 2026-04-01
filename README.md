# IApel — Gestion de Documents d'Intervention

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-blue.svg) ![Laravel 11](https://img.shields.io/badge/Laravel-11-red.svg) ![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)

IApel est une plateforme web développée pour **APEL Bâtiment**, permettant la dématérialisation et la gestion sécurisée des documents d'intervention (BI), des devis et des signatures électroniques.

## Sommaire
- [Aperçu](#aperçu)
- [Fonctionnalités clés](#fonctionnalités-clés)
- [Tech & prérequis](#tech--prérequis)
- [Installation rapide (Windows)](#installation-rapide-windows)
- [Utilisation & API](#utilisation--api)
- [Authentification API (Clients externes)](#authentification-api-clients-externes)
- [Documentation Technique](#documentation-technique)
- [Commandes utiles](#commandes-utiles)
- [Base de données](#base-de-données)
- [Licence](#licence)

---

## Aperçu
L'application sert de pont entre les techniciens sur le terrain et les clients finaux. Elle permet de générer des rapports d'intervention au format PDF, de faire signer des devis à distance via des liens sécurisés par jetons (tokens), et de gérer le suivi des documents.

---

## Fonctionnalités clés
- **Bons d'Intervention (BI)** : Saisie de formulaires en ligne et génération instantanée de rapports PDF.
- **Signature Électronique** : Validation et certification de devis par les clients via une interface dédiée.
- **Gestion par Tokens** : Accès sécurisé et temporaire aux documents sans nécessité de compte client complet.
- **Gestion de Photos** : Upload et compression de visuels liés aux interventions.
- **Outils d'Administration** : Scripts utilitaires (Python/C) pour la gestion des clients et des layouts.
- **API sécurisée par clé** : Intégration entreprises tierces via clés API hachées (SHA-256) avec mise en cache.

---

## Tech & prérequis
- **Backend** : PHP 8.2+ / Laravel 11
- **Frontend** : Blade Templates, Tailwind CSS, Vite
- **Base de données** : SQLite (dev), MySQL/PostgreSQL (prod)
- **PDF** : Intégration FPDI / TCPDF pour la manipulation et la certification.
- **Documentation** : Doxygen pour la génération de la doc technique.

---

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

4. **Initialiser la base de données** (SQLite par défaut)
   ```bash
   php artisan migrate --seed
   ou 
   php artisan migrate:fresh
   ```

5. **Lier le stockage** (Crucial pour les PDFs et images)
   ```bash
   php artisan storage:link
   ```

6. **Lancer le serveur**
   ```bash
   php artisan serve
   ```

---

## Utilisation & API
L'application expose plusieurs points d'entrée pour l'intégration avec des outils tiers :
- `POST /api/create-json` : Génère un jeton d'accès sécurisé pour les bi/cerfa.
- `POST /api/generate-token` : Génère un jeton d'accès sécurisé pour les devis.
- `GET /bi/{token}` : Affiche le formulaire d'intervention pour un technicien.
- `GET /signature/{token}` : Interface de signature pour le client (devis).
- `GET /pdf/{token}` : Récupération du rapport final.

---

## Authentification API (Clients externes)

Les routes API protégées utilisent le middleware `AuthClientApiKeyMiddleware`. Ce système permet à des entreprises partenaires d'accéder à l'API de manière sécurisée via une clé API propre à chacune.

### Fonctionnement du middleware

Le middleware accepte la clé API via deux méthodes :
- **Header personnalisé** : `X-API-KEY: <votre_clé>`
- **Standard Bearer** : `Authorization: Bearer <votre_clé>`

La clé est immédiatement hachée en SHA-256 avant toute comparaison — la clé en clair n'est jamais stockée en base de données. Le résultat de la recherche est mis en cache pendant **150 secondes** pour limiter la charge sur la base de données lors d'appels fréquents.

```
Requête entrante
    │
    ├─ Lecture du header (X-API-KEY ou Authorization: Bearer)
    ├─ Hachage SHA-256 de la clé
    ├─ Recherche dans le cache (TTL: 150s) → si absent, requête en BDD
    ├─ Vérification existence du client
    ├─ Vérification du statut is_active
    └─ Injection du client dans $request->attributes['api_client']
```

### Commandes Artisan — Gestion des clés API

Toute la gestion des clés API se fait via des commandes Artisan (pas d'interface admin exposée).

#### Créer une clé pour une nouvelle entreprise

```bash
php artisan api:create-api-key {entreprise}
```

La commande génère une clé aléatoire sécurisée, la hache en SHA-256, persiste le hash en base et **affiche la clé en clair une seule fois** dans le terminal. Cette clé doit être transmise immédiatement et de façon sécurisée à l'entreprise concernée — elle ne pourra plus être récupérée ensuite.

```bash
# Exemple
php artisan api:create-api-key "MonEntrepriseSAS"
# → Clé API créée pour l'entreprise 'MonEntrepriseSAS': v1wVRMfIYJoakC...
```

> ⚠️ **Important** : si une clé existe déjà pour cette entreprise, la commande échoue pour éviter les doublons. Utilisez `api:update-api-key` pour renouveler.

#### Renouveler la clé d'une entreprise existante

```bash
php artisan api:update-api-key {entreprise}
```

Génère une nouvelle clé et remplace le hash en base. L'ancienne clé est immédiatement invalidée. La nouvelle clé en clair est affichée une seule fois.

```bash
# Exemple
php artisan api:update-api-key "MonEntrepriseSAS"
# → Clé API mise à jour pour l'entreprise 'MonEntrepriseSAS': xK9pLmN3...
```

#### Activer / Désactiver un client

```bash
php artisan api:toggle-client {identifier}
```

`{identifier}` accepte soit l'**ID numérique** soit le **nom de l'entreprise**. La commande inverse le statut `is_active` du client. Un client désactivé reçoit une erreur `401` à chaque appel, sans que sa clé soit supprimée.

```bash
# Par nom d'entreprise
php artisan api:toggle-client "MonEntrepriseSAS"
# → ⚠️ L'entreprise 'MonEntrepriseSAS' a été DÉSACTIVÉE avec succès.

# Par ID
php artisan api:toggle-client 3
# → ✅ L'entreprise 'MonEntrepriseSAS' a été ACTIVÉE avec succès.
```

### Récupérer le client dans un contrôleur

Une fois le middleware passé, les informations du client API sont disponibles directement depuis la requête, sans nouvelle requête SQL :

```php
$apiClient = $request->attributes->get('api_client');
// $apiClient est une instance de App\Models\Api_Client
```

---

## Documentation Technique
Le projet utilise **Doxygen** pour maintenir une documentation à jour du code source.
Pour générer ou consulter la doc :
1. Assurez-vous d'avoir Doxygen installé.
2. Utilisez le fichier `Doxyfile` à la racine.
3. Les thèmes *doxygen-awesome* sont inclus pour un rendu moderne.

---

## Commandes utiles

| Commande                                    | Description                          |
|---------------------------------------------|--------------------------------------|
| `php artisan optimize:clear`                | Vider tous les caches                |
| `php artisan test`                          | Lancer la suite de tests             |
| `php artisan migrate`                       | Migre la base de donnés              |
| `php artisan api:create-api-key {nom}`      | Créer une clé API entreprise         |
| `php artisan api:update-api-key {nom}`      | Renouveler une clé API               |
| `php artisan api:toggle-client {id\|nom}`   | Activer / désactiver un client API   |

---

## Base de données
- **Développement** : `database/database.sqlite`
- **Production** : Configurez vos accès `DB_*` dans le fichier `.env`.

---

## Crédits
Développé par **El2i Informatique** pour le projet **APEL Bâtiment**.
Site web : [https://www.el2i.fr](https://www.el2i.fr)