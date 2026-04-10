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
        $clientKey = $request->header('X-API-KEY') ?? $request->bearerToken();

        if (! $clientKey) {
            return $this->unauthorizedResponse($request, 'Accès refusé : Clé API manquante');
        }

        $hashedKey = hash('sha256', $clientKey);

        // Tenter de récupérer le client depuis le cache, sinon interroger la base de données, et stocker le résultat en cache pour 150 secondes
        $client = Cache::remember('api_key_' . $hashedKey, 150, function () use ($hashedKey) {
            return Api_Client::where('token_hash', $hashedKey)->first();
        });

        if (! $client) {
            return $this->unauthorizedResponse($request, 'Accès refusé : Clé API invalide');
        }

        if (! $client->is_active) {
            return $this->unauthorizedResponse($request, 'Accès refusé : Clé API inactive');
        }

        $request->attributes->add(['api_client' => $client]);

        return $next($request);
    }

    /**
     * Gère la réponse d'erreur en fonction du type de requête attendu.
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 401);
        }

        abort(401, $message);
    }
}

/*
 * Pour les tests, la clé API de test est : 
 * Clé API       : v1wVRMfIYJoakCMI9dLfySr6uZqqMOXkWuoH6GsFxRLU6kUyVsrcdl3bmVWM
 * Clé API Hash  : 98931099302b39c0db3314ea3a1e941a7345747e3a3f82268e3e55cf75c18a5c
*/