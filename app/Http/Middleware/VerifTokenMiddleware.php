<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Log;

class VerifTokenMiddleware
{
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    public function handle(Request $request, Closure $next): Response
    {
        $entreprise = $request->organisation_id ?? $request->client ?? $request->entreprise ?? null;
        $token = $request->route('token');

        \Log::info("[AUTH] VERIFICATION TOKEN", [
            'token' => $token,
            'entreprise' => $entreprise,
            'route' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        if (!TokenController::isValideTokenRapport($token)) {
            \Log::warning("[AUTH] TOKEN MANQUANT OU INVALIDE", [
                'token' => $token,
                'client' => $entreprise,
                'route' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            abort(401, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        return $next($request);
    }

}
