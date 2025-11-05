<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Log;

/**
 * @class VerifTokenMiddleware
 * @brief Middleware de vérification des tokens d'accès aux rapports et documents.
 *
 * Ce middleware assure la sécurité d'accès aux documents sensibles (rapports d'intervention,
 * formulaires CERFA) en validant les tokens fournis dans les URLs. Il constitue la première
 * couche de protection pour empêcher l'accès non autorisé aux documents générés par l'application.
 * 
 * Fonctionnalités principales :
 * - Validation de l'existence et de la validité des tokens dans les routes.
 * - Vérification de l'expiration des tokens d'accès.
 * - Logging automatique des tentatives d'accès pour audit de sécurité.
 * - Blocage immédiat des accès avec tokens invalides ou expirés.
 * - Intégration transparente avec le système de routage Laravel.
 * - Support des tokens de type TokenLinksRapport pour documents BI.
 *
 * @package App\Http\Middleware
 * @version 1.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Utilisé pour protéger les routes d'accès aux documents via tokens.
 * @note Logs toutes les tentatives de vérification pour traçabilité.
 * @warning Retourne une erreur 404 si le token est invalide pour masquer l'existence de la ressource.
 * @see TokenController::isValideTokenRapport() Pour la logique de validation des tokens.
 * @see TokenLinksRapport Pour le modèle de gestion des tokens de rapports.
 * @example
 * ```php
 * // Utilisation dans les routes (routes/web.php)
 * Route::get('/bi/{token}', [BiController::class, 'show'])
 *      ->middleware('verify.token');
 * 
 * Route::get('/download/{token}', [BiController::class, 'download'])
 *      ->middleware('verify.token');
 * ```
 */
class VerifTokenMiddleware
{
    /**
     * @brief Traite une requête entrante et vérifie la validité du token.
     *
     * Cette méthode constitue le point d'entrée du middleware. Elle extrait le token
     * depuis les paramètres de route, le valide via TokenController, et décide
     * d'autoriser ou de bloquer l'accès à la ressource demandée.
     *
     * Le processus de vérification suit ces étapes :
     * 1. Extraction du token depuis les paramètres de route.
     * 2. Logging de la tentative d'accès pour audit.
     * 3. Validation du token via TokenController::isValideTokenRapport().
     * 4. Autorisation ou refus d'accès selon le résultat de validation.
     *
     * @param Request $request Requête HTTP entrante contenant le token en paramètre de route.
     * @param Closure $next Fonction de callback pour passer au middleware suivant ou au contrôleur.
     * 
     * @return Response Réponse HTTP (404 si token invalide, sinon passage au contrôleur).
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si le token est invalide.
     * 
     * @example
     * ```php
     * // URL d'accès typique
     * https://app.example.com/bi/abc123def456...
     * 
     * // Le middleware extrait 'abc123def456...' et le valide
     * // Si valide : accès autorisé au BiController::show()
     * // Si invalide : erreur 404 retournée
     * ```
     * 
     * @example
     * ```php
     * // Configuration dans le kernel HTTP (app/Http/Kernel.php)
     * protected $middlewareAliases = [
     *     'verify.token' => \App\Http\Middleware\VerifTokenMiddleware::class,
     * ];
     * 
     * // Protection de multiple routes
     * Route::group(['middleware' => 'verify.token'], function () {
     *     Route::get('/bi/{token}', [BiController::class, 'show']);
     *     Route::get('/download/{token}', [BiController::class, 'download']);
     *     Route::post('/submit/{token}', [BiController::class, 'submit']);
     * });
     * ```
     * 
     * @note Le token est automatiquement extrait depuis $request->route('token').
     * @note Toutes les tentatives sont loggées avec le niveau INFO pour audit.
     * @note L'erreur 404 est préférée à 403 pour ne pas révéler l'existence de la ressource.
     * @warning Ne pas utiliser ce middleware sur des routes ne contenant pas de paramètre {token}.
     * @warning Les logs peuvent contenir des tokens sensibles, attention à leur protection.
     * @see Log::info() Pour l'enregistrement des tentatives d'accès.
     * @see abort() Pour le déclenchement de l'erreur HTTP 404.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        Log::info("Demande de verification du token : " . $token);

        if (!TokenController::isValideTokenRapport($token)) {
            abort(404, 'Token invalide');
        }

        return $next($request);
    }
}
