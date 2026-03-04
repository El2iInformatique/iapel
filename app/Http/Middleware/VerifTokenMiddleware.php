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
        $token = $request->route('token');
        Log::info("Demande de verification du token : " . $token);

        if (!TokenController::isValideTokenRapport($token)) {
            Log::error("[VerifTokenMiddleware - HANDLE] Token introuvable : $token");
            abort(404, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        return $next($request);
    }

}
