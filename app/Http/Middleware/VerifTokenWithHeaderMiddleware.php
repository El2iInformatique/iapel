<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\TokenController;
use App\Models\TokenLinksRapport;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * @class VerifTokenWithHeaderMiddleware
 * @brief Middleware de vérification avancée combinant token d'URL et authentification par mot de passe.
 *
 * Ce middleware implémente une double vérification de sécurité pour l'accès aux documents sensibles.
 * Il combine la validation du token d'accès (comme VerifTokenMiddleware) avec une authentification
 * supplémentaire via un mot de passe transmis dans les en-têtes HTTP. Cette approche multicouche
 * offre une protection renforcée pour les opérations critiques nécessitant une autorisation élevée.
 * 
 * Fonctionnalités principales :
 * - Double authentification : token d'URL + mot de passe dans les en-têtes.
 * - Support des mots de passe spécifiques par client et mot de passe administrateur.
 * - Validation du token de rapport via TokenController.
 * - Vérification de l'existence des fichiers JSON associés.
 * - Extraction automatique des informations client depuis les données du token.
 * - Logging détaillé des tentatives d'accès pour audit de sécurité.
 * - Comparaison sécurisée des mots de passe avec hash_equals().
 *
 * @package App\Http\Middleware
 * @version 1.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Utilisé pour les opérations sensibles nécessitant une authentification renforcée.
 * @note Supporte à la fois les mots de passe clients et le mot de passe administrateur global.
 * @warning Requiert la configuration des secrets clients dans config/secrets.php.
 * @see TokenController::isValideTokenRapport() Pour la validation des tokens.
 * @see TokenLinksRapport Pour la récupération des métadonnées des tokens.
 * @example
 * ```php
 * // Utilisation dans les routes (routes/web.php)
 * Route::delete('/delete/{token}', [BiController::class, 'delete'])
 *      ->middleware('verify.token.header');
 * 
 * Route::post('/admin/{token}', [AdminController::class, 'manage'])
 *      ->middleware('verify.token.header');
 * ```
 */
class VerifTokenWithHeaderMiddleware
{
    /**
     * @brief Traite une requête entrante avec double vérification token + en-tête.
     *
     * Cette méthode principale du middleware effectue une validation en deux étapes :
     * d'abord la vérification du token d'URL, puis l'authentification par mot de passe
     * transmis dans les en-têtes HTTP. Elle assure ainsi une sécurité renforcée pour
     * les opérations critiques de l'application.
     *
     * Le processus de vérification suit ces étapes :
     * 1. Extraction du token depuis les paramètres de route.
     * 2. Logging de la tentative d'accès pour audit.
     * 3. Appel de la méthode privée VerifTokenWithHeader() pour validation complète.
     * 4. Autorisation ou refus d'accès selon le résultat de validation.
     *
     * @param Request $request Requête HTTP entrante contenant token et en-têtes d'authentification.
     * @param Closure $next Fonction de callback pour passer au middleware suivant ou au contrôleur.
     * 
     * @return Response Réponse HTTP (404 si échec, sinon passage au contrôleur).
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si validation échoue.
     * 
     * @example
     * ```php
     * // Requête HTTP avec en-têtes requis
     * curl -H "secret-token: mot_de_passe_client" \
     *      -X DELETE \
     *      https://app.example.com/delete/abc123def456...
     * ```
     * 
     * @note Utilise la méthode privée VerifTokenWithHeader() pour la logique de validation.
     * @note Logs toutes les tentatives avec le token pour traçabilité complète.
     * @warning Retourne 404 au lieu de 401/403 pour masquer l'existence de la ressource.
     * @see VerifTokenWithHeader() Pour la logique détaillée de validation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        Log::info("Demande de verification du token : " . $token);

        if (!self::VerifTokenWithHeader($token, $request)) {
            abort(404, 'Token invalide ou mauvais mot de passe');
        }

        return $next($request);
    }

    /**
     * @brief Effectue la validation complète du token et de l'authentification par en-tête.
     *
     * Cette méthode privée implémente la logique centrale de validation du middleware.
     * Elle vérifie successivement la présence de l'en-tête d'authentification, la validité
     * du token, l'existence des fichiers associés, et enfin la correspondance du mot de passe
     * fourni avec celui configuré pour le client ou l'administrateur.
     *
     * Le processus de validation suit ces étapes critiques :
     * 1. Vérification de la présence de l'en-tête 'secret-token'.
     * 2. Validation du token via TokenController::isValideTokenRapport().
     * 3. Récupération des métadonnées du token depuis TokenLinksRapport.
     * 4. Vérification de l'existence du fichier JSON associé.
     * 5. Extraction du nom du client depuis les données JSON.
     * 6. Récupération des mots de passe configurés (client + admin).
     * 7. Comparaison sécurisée des mots de passe avec hash_equals().
     *
     * @param string $token Token d'accès extrait de l'URL de la requête.
     * @param Request $request Requête HTTP contenant les en-têtes d'authentification.
     * 
     * @return bool True si toutes les validations passent, false sinon.
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Avec code 401 si en-tête manquant.
     * 
     * @example
     * ```php
     * // Structure attendue du fichier JSON référencé par le token
     * {
     *   "dataToken": {
     *     "client": "entreprise1",
     *     "document": "rapport_intervention",
     *     "uid": "unique_id_123"
     *   },
     *   // ... autres données du document
     * }
     * 
     * // Configuration requise dans config/secrets.php
     * return [
     *     'admin' => 'mot_de_passe_admin_global',
     *     'entreprise1' => 'mot_de_passe_entreprise1',
     *     'entreprise2' => 'mot_de_passe_entreprise2',
     * ];
     * ```
     * 
     * @example
     * ```php
     * // Test manuel de la validation
     * $request = new Request();
     * $request->headers->set('secret-token', 'mot_de_passe_correct');
     * 
     * $isValid = VerifTokenWithHeaderMiddleware::VerifTokenWithHeader(
     *     'abc123def456...',
     *     $request
     * );
     * ```
     * 
     * @note Utilise hash_equals() pour éviter les attaques de timing sur les mots de passe.
     * @note Le mot de passe administrateur permet l'accès à tous les documents clients.
     * @note Les mots de passe client sont récupérés via config('secrets.' . $client).
     * @warning Les mots de passe sont loggés en clair, attention à la sécurité des logs.
     * @warning Un client sans mot de passe configuré utilisera "nulls" par défaut.
     * @see hash_equals() Pour la comparaison sécurisée des chaînes sensibles.
     * @see config() Pour l'accès aux configurations de mots de passe.
     * @see TokenLinksRapport::where() Pour la récupération des métadonnées des tokens.
     */
    private function VerifTokenWithHeader($token, Request $request): bool
    {
        
        if (!$request->header('secret-token')) {
            abort(401, "Mot de passe manquant");
        }
        if (!TokenController::isValideTokenRapport($token)) {
            return false;
        }

        $dataToken = TokenLinksRapport::where('token', $token)->first();
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            return false;
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];

        $secretToken = $request->header('secret-token');

        $adminPassword = config('secrets.admin');
        $clientPassword = config('secrets.' . $client) ?? "nulls";

        Log::info("Secret token : " . $secretToken);


        if (!hash_equals($clientPassword,$secretToken) && !hash_equals($adminPassword, $secretToken)) {
            return false;
        }

        return true;
    }
}
