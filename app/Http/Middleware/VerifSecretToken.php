<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifSecretToken
{
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    public function handle(Request $request, Closure $next): Response
    {
        $entreprise = $request->organisation_id ?? $request->client ?? $request->entreprise ?? null;

        \Log::info("[AUTH] VERIFICATION SECRET-TOKEN", [
            'entreprise' => $entreprise,
            'route' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        if (!self::verifSecretToken($request)) {
            \Log::warning("[AUTH] SECRET-TOKEN MANQUANT OU INVALIDE", [
                'entreprise' => $entreprise,
                'route' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            return response()->json(  'Not authorized.',  401,['Content-Type' => 'application/json']);
        }

        return $next($request);
    }


    private function verifSecretToken(Request $request): bool
    {
        $providedToken = $request->header('secret-token');

        if (!$providedToken) {
            return false;
        }

        // On récupère les tokens valides (on filtre les valeurs nulles au cas où)
        $validTokens = array_filter([
            config("secrets.{$request->organisation_id}"),
            config('secrets.admin'),
        ]);

        // On vérifie si le token fourni correspond à l'un des tokens autorisés
        foreach ($validTokens as $token) {
            if (hash_equals($token, $providedToken)) {
                return true;
            }
        }

        return false;
    }

}
