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

            if (!TokenController::validateToken($token)) {
                abort(404, 'Token invalide');
            }

            /* Protection contre l'utilisation de token d'un document sur un autre type de document
            $tokenReport = Token::where('token', '=', $token);
            if (str_contains($request->url(), "/rapport-cerfa/bi/")) {
                if ($tokenReport->type_document === "rapport_intervention" || $tokenReport->type_document === "cerfa") {
                    return $next($request);
                }
            }
            if (str_contains($request->url(), "/signature/")) {
                if ($tokenReport->type_document === "rapport_intervention" || $tokenReport->type_document === "cerfa") {
                    return $next($request);
                }
            }
            abort(406, "Token invalide dans ce contexte");
            */

            return $next($request);
        } catch (\Throwable $th) {
            return response()->json(["message" => "Impossible d'accéder à la ressource demandée"], 404);
        }
    }

}
