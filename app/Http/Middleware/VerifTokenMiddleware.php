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
     *
     * 
     * Middleware permettant de vérifier si un token est mis dans les paramètres de la route et si ce token est valide
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            
            // Récupération du token dans les paramètres de la route
            $token = $request->route('token');
            Log::info("Demande de verification du token : " . $token);

            // Test si le token existe et renvoie une réponse
            $response = TokenController::validateToken($token);

            // Regarde si le statut de la réponse est ok
            if ($response->getStatusCode() != 200) {
                // sinon abort avec une erreur forbidden
                abort(403, 'Token invalide');
            }

            // Log et continue
            Log::info("Token valider");
            return $next($request);

        } catch (\Throwable $th) {
            Log::info("Erreur : " . $th->getMessage());
            return response()->json(["message" => "Impossible d'accéder à la ressource demandée"], 404);
        }
    }

}
