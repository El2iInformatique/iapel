<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

/**
 * @class AntiSpamMiddleware
 * @brief Middleware de protection contre les attaques par déni de service et le spam.
 *
 * Ce middleware implémente un système de limitation de taux (rate limiting) pour protéger
 * l'application contre les tentatives de spam et les attaques par déni de service (DoS).
 * Il utilise le système de cache de Laravel pour suivre le nombre de requêtes par IP
 * et par URL, bloquant temporairement les utilisateurs qui dépassent les limites définies.
 * 
 * Fonctionnalités principales :
 * - Limitation du nombre de requêtes par IP et par route.
 * - Configuration flexible des seuils de déclenchement et de la durée de blocage.
 * - Utilisation du cache Laravel pour un suivi efficace des tentatives.
 * - Réinitialisation automatique des compteurs après expiration.
 * - Réponse HTTP 429 (Too Many Requests) conforme aux standards.
 * - Protection granulaire par route avec paramètres personnalisables.
 *
 * @package App\Http\Middleware
 * @version 1.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Middleware actuellement non utilisé selon le commentaire dans le code.
 * @note Utilise le système de cache configuré dans l'application Laravel.
 * @warning Peut bloquer des utilisateurs légitimes en cas de configuration trop restrictive.
 * @see Cache Pour le stockage des compteurs de tentatives.
 * @example
 * ```php
 * // Utilisation dans les routes (routes/web.php)
 * Route::post('/contact', [ContactController::class, 'store'])
 *      ->middleware('antispam:10,5'); // 10 tentatives max en 5 minutes
 * 
 * // Utilisation avec paramètres par défaut
 * Route::post('/login', [AuthController::class, 'login'])
 *      ->middleware('antispam'); // 5 tentatives max en 1 minute
 * ```
 */
class AntiSpamMiddleware
{
    /**
     * @brief Traite une requête entrante et applique la limitation de taux.
     *
     * Cette méthode constitue le cœur du middleware. Elle vérifie le nombre de tentatives
     * effectuées par l'utilisateur depuis la même adresse IP vers la même URL, et bloque
     * l'accès si le seuil autorisé est dépassé. Le système utilise une clé composite
     * incluant l'URL complète et l'adresse IP pour un suivi granulaire.
     *
     * Le processus de vérification suit ces étapes :
     * 1. Génération d'une clé unique basée sur l'URL et l'IP de l'utilisateur.
     * 2. Incrémentation du compteur de tentatives dans le cache.
     * 3. Configuration de l'expiration du compteur lors de la première tentative.
     * 4. Vérification du dépassement du seuil autorisé.
     * 5. Blocage ou autorisation de la requête selon le résultat.
     *
     * @param Request $request Requête HTTP entrante à analyser.
     * @param Closure $next Fonction de callback pour passer au middleware suivant.
     * @param int $maxAttempts Nombre maximum de tentatives autorisées (défaut: 5).
     * @param int $decayMinutes Durée en minutes avant réinitialisation du compteur (défaut: 1).
     * 
     * @return Response Réponse HTTP (429 si bloqué, sinon passage au middleware suivant).
     * 
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException Si le cache est indisponible.
     * 
     * @example
     * ```php
     * // Configuration dans le kernel HTTP (app/Http/Kernel.php)
     * protected $middlewareAliases = [
     *     'antispam' => \App\Http\Middleware\AntiSpamMiddleware::class,
     * ];
     * 
     * // Utilisation restrictive pour les formulaires sensibles
     * Route::post('/reset-password', function() {
     *     // Logic here
     * })->middleware('antispam:3,10'); // 3 tentatives max en 10 minutes
     * ```
     * 
     * @example
     * ```php
     * // Utilisation pour protéger les API publiques
     * Route::apiResource('users', UserController::class)
     *      ->middleware('antispam:50,60'); // 50 requêtes max par heure
     * ```
     * 
     * @note La clé de cache inclut l'URL complète pour permettre différents seuils par endpoint.
     * @note Le premier appel à Cache::increment() retourne 1 et déclenche la configuration d'expiration.
     * @note Les tentatives sont comptées par combinaison IP + URL, pas globalement par IP.
     * @warning Une configuration trop restrictive peut nuire à l'expérience utilisateur.
     * @warning Le middleware ne distingue pas les requêtes légitimes des tentatives malveillantes.
     * @see Cache::increment() Pour l'incrémentation atomique des compteurs.
     * @see now()->addMinutes() Pour la gestion de l'expiration des compteurs.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        // On utilise l'URL de la route et l'adresse IP de l'utilisateur.
        $key = 'anti-spam:' . $request->fullUrl() . ':' . $request->ip();

        // Cache::increment() retourne la nouvelle valeur après incrémentation.
        $attempts = Cache::increment($key);

        // Cela permet de réinitialiser le compteur après un certain temps.
        if ($attempts === 1) {
            Cache::put($key, 1, now()->addMinutes($decayMinutes));
        }

        if ($attempts > $maxAttempts) {
            // return redirect()->back()->withErrors(['spam' => 'Trop de tentatives. Veuillez réessayer plus tard.']);
            // ou
            return response('Trop de tentatives de spam. Veuillez réessayer plus tard.', 429); // 429 Too Many Requests
        }

        // 5. Si pas de spam détecté, passer la requête au middleware suivant ou au contrôleur
        return $next($request);
    }
}