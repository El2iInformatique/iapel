<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\TokenController;
use App\Models\Token;


use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;


class VerifTokenWithHeaderMiddleware
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
        // Récupération du token dans les paramètres de la route
        $token = $request->route('token');
        Log::info("Demande de verification du token : " . $token);

        // Appelle la fonction VerifTokenWithHeader qui vérifie la présence du header et du token, vérifie s'ils sont valides
        if (!self::VerifTokenWithHeader($token, $request)) {
            abort(404, 'Token invalide ou mot de passe incorrect');
        }

        // Continue
        return $next($request);
    }

    // Fonction de verification du header et du token
    private function VerifTokenWithHeader($token, Request $request): bool
    {
        // Vérifie l'existence du header
        if (!$request->header('secret-token')) {
            abort(403, "Mot de passe manquant");
        }

        // Vérifie l'existence du token
        if (!TokenController::validateToken($token)) {
            return false;
        }

        // Récupère les informations du token
        $dataToken = Token::where('token', $token)->first();
        if (!$dataToken) {
            return false;
        }
        
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            return false;
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];

        $secretToken = $request->header('secret-token');

        // Récupère les mots de passe admin et celui du client
        $adminPassword = config('secrets.admin');
        $clientPassword = config('secrets.' . $client) ?? "null";

        // Vérifie si les mots de passe enregistrés sont les mêmes que ceux envoyés
        if (!hash_equals($clientPassword,$secretToken) && !hash_equals($adminPassword, $secretToken)) {
            return false;
        }

        return true;
    }
}
