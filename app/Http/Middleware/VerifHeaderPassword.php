<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifHeaderPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * 
     * Middleware permettant de vérifier si un header secret-token existe et un token dans les paramètres de la route existe et si celui-ci est valide
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifie qu'un header secret-token existe
        if (!$request->hasHeader('secret-token')) {
            return response()->json(['error' => 'No secret token provided.'], 403);
        }

        // Récupère les mots de passe admin et celui du client
        $secretToken = config("secrets.$request->organisation_id") ?? "None";
        $adminToken = config('secrets.admin');

        $providedToken = $request->header('secret-token');

        // Vérifie si les mots de passe enregistrés sont les mêmes que ceux envoyés
        if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
            return response()->json(['error' => 'Not authorized.'], 401);
        }
        
        // Continue
        return $next($request);
    }
}
