<?php

namespace App\Http\Middleware;

use App\Models\Api_Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Ajout de la façade Cache
use Symfony\Component\HttpFoundation\Response;

class AuthClientApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupération de la clé API
        // Supporte le header personnalisé "X-API-KEY" ou le standard "Authorization: Bearer <token>"
        $clientKey = $request->header("X-API-KEY") ?? $request->bearerToken();

        if (!$clientKey) {
            return response()->json(['error' => 'Accès refusé : Clé API manquante'], 401);
        }
        
        // Hachage de la clé (Sécurité)
        // On ne compare que des hashs pour qu'en cas de fuite de la BDD, les clés en clair restent sûres.
        $hashedKey = hash('sha256', $clientKey);

        // Récupération du client avec mise en cache (Performances)
        // On garde le résultat en mémoire pendant 5 minutes (300 secondes).
        // Cela évite de solliciter la base de données à chaque appel API pour un même client.
        $storedKey = Cache::remember('api_key_' . $hashedKey, 150, function () use ($hashedKey) {
            return Api_Client::where('token_hash', $hashedKey)->first();
        });

        // Vérification de l'existence de la clé en base de données
        if (!$storedKey) {
            return response()->json(['error' => 'Accès refusé : Client inconnu'], 401);
        }

        // Vérification du statut d'activation du client
        if (!$storedKey->is_active) {
            return response()->json(['error' => 'Accès refusé : Clé API inactive'], 401);
        }

        // Permet au contrôleur final de savoir qui fait la requête sans refaire de requête SQL.
        $request->attributes->add(['api_client' => $storedKey]);

        // La clé est valide, on passe à l'étape suivante (le contrôleur ou le prochain middleware)
        return $next($request);
    }
}

/*
 * Pour les tests, la clé API de test est : 
 * Clé API       : v1wVRMfIYJoakCMI9dLfySr6uZqqMOXkWuoH6GsFxRLU6kUyVsrcdl3bmVWM
 * Clé API Hash  : 98931099302b39c0db3314ea3a1e941a7345747e3a3f82268e3e55cf75c18a5c
*/