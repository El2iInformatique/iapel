<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\TokenLinks;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;



class TokenController extends Controller
{


    // Validation du token avant affichage de l’interface de signature
    public function validateToken($token)
    {
        $tokenEntry = TokenLinks::where('token', $token)->first();

        if (!$tokenEntry) {
            return response()->json(['message' => 'Token invalide ou expiré'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'Token valide', 'devis_id' => $tokenEntry->devis_id], 200);
    }


    /**
     * @brief Génère un token pour accéder à un rapport d'intervention ou document PDF.
     * 
     * Contrairement aux tokens de signature (devis), ceux-ci permettent simplement
     * l'accès en lecture sécurisée à un document déjà généré.
     */
    public static function generateToken($path, string $documents = "__None__"){
        
        // === GÉNÉRATION D'UN TOKEN SÉCURISÉ ===
        // Crée une chaîne aléatoire de 60 caractères (cryptographiquement sûre)
        // Cette clé sera stockée en base avec l'expiration et le chemin du document
        $token = Str::random(60);

        // Enregistre le token en base de données avec chemin et date d'expiration
        TokenLinks::generateToken($token, $path, $documents);

        return $token;
    }

    /**
     * @brief Valide qu'un token d'accès au rapport existe et n'a pas expiré.
     * 
     * Vérifie :
     * 1. Que le token existe en base de données
     * 2. Que la date d'expiration n'est pas dépassée
     * Supprime automatiquement les tokens expirés.
     */
    public static function isValideTokenRapport($token)
    {
        // === RECHERCHE DU TOKEN ===
        $tokenRecord = TokenLinks::where('token', $token)->first();

        // Token inexistant → accès refusé
        if (!$tokenRecord)
            return false;

        // === VÉRIFICATION DE L'EXPIRATION ===
        $expiresAt = Carbon::parse($tokenRecord->expires_at);

        // Si le token a expiré, on le supprime de la base (nettoyage)
        if ($expiresAt->lessThan(now())) {
            \Log::info("[TOKEN] DATE DEPASSER", [
                'token' => $tokenRecord->token,
                'client' => $tokenRecord->client
            ]);

            $tokenRecord->delete();
            return false;
        }

        return true;
    }

    public function getToken(Request $request, $client, $document, $uid){

        $tokenEntry = TokenLinks::where('paths', 'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json')->first();

        if (!$tokenEntry) {
            return response()->json(['message' => 'token valide', 'token' => $tokenEntry->token,], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'token valide', 'token' => $tokenEntry->token,], Response::HTTP_OK);
    }

}
