<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\TokenController;
use App\Models\Token;
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
        try {
            
            $token = $request->route('token');
            Log::info("Demande de verification du token : " . $token);

            $response = TokenController::validateToken($token);

            if ($response->getStatusCode() != 200) {
                abort(403, 'Token invalide');
            }

            Log::info("Token valide");
            return $next($request);

        } catch (\Throwable $th) {
            Log::info("Erreur : " . $th->getMessage());
            return response()->json(["message" => "Impossible d'accéder à la ressource demandée"], 404);
        }
    }

}
