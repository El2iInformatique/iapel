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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        Log::info("Demande de verification du token : " . $token);

        if (!self::VerifTokenWithHeader($token, $request)) {
            abort(404, 'Token invalide ou mot de passe incorrect');
        }

        return $next($request);
    }

    private function VerifTokenWithHeader($token, Request $request): bool
    {
        
        if (!$request->header('secret-token')) {
            abort(403, "Mot de passe manquant");
        }
        if (!TokenController::validateToken($token)) {
            return false;
        }

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

        $adminPassword = config('secrets.admin');
        $clientPassword = config('secrets.' . $client) ?? "null";

        Log::info("Secret token : " . $secretToken);


        if (!hash_equals($clientPassword,$secretToken) && !hash_equals($adminPassword, $secretToken)) {
            return false;
        }

        return true;
    }
}
