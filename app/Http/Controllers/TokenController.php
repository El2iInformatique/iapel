<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\TokenLinksRapport;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;



class TokenController extends Controller
{

    /**
     * @brief Génère un token de signature sécurisé pour un devis.
     * 
     * Le token est une clé unique et temporaire permettant au client de signer le devis
     * sans être authentifié. Il expire après 30 jours.
     */
    public function generate(Request $request)
    {
        // === VALIDATION DES DONNÉES REQUISES ===
        $request->validate([
            'organisation_id' => 'required|string',  // Identifiant du prestataire
            'devis_id' => 'required|string',         // Numéro unique du devis
            'tiers' => 'required|string',
            'client_email' => 'required|string',
            'titre' => 'required|string',
            'montant_HT' => 'required|numeric|min:0',
            'montant_TVA' => 'required|numeric|min:0',
            'montant_TTC' => 'required|numeric|min:0',
            'coords' => 'required|json',
            'nb_pages' => 'required|numeric|min:0',
        ]);

        \Log::info("[DOCUMENT] DEBUT GENERATION TOKEN", [
            'organisation_id' => $request->organisation_id,
            'devis_id' => $request->devis_id,
            'request_data' => $request->all()
        ]);

        $coords = json_decode($request->input('coords'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in coords'], 400);
        }

        $token = Token::generateToken(
            $request->organisation_id,
            $request->tiers,
            $request->devis_id,
            $request->client_email,
            $request->titre,
            $request->montant_HT,
            $request->montant_TVA,
            $request->montant_TTC,
            $coords,
            $request->nb_pages,
        );

        return response()->json([
            'message' => 'Token généré avec succès',
            'token' => $token,
            'signature_url' => url('/signature/' . $token->token),
        ]);
    }

    // Validation du token avant affichage de l’interface de signature
    public function validateToken($token)
    {
        $tokenEntry = Token::where('token', $token)->where('used', false)->where('expires_at', '>', now())->first();

        if (!$tokenEntry) {
            return response()->json(['message' => 'Token invalide ou expiré'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'Token valide', 'devis_id' => $tokenEntry->devis_id]);
    }


    /**
     * @brief Génère un token pour accéder à un rapport d'intervention ou document PDF.
     * 
     * Contrairement aux tokens de signature (devis), ceux-ci permettent simplement
     * l'accès en lecture sécurisée à un document déjà généré.
     */
    public static function generateTokenRapport(Request $request, $path){
        // === AUTHENTIFICATION (actuellement commentée) ===
        // Code d'authentification désactivé - à réactiver si besoin de vérifier
        // que le demandeur est autorisé à générer des tokens
        /*
            if (!$request->hasHeader('secret-token')) {
                return response()->json(['error' => 'No secret token provided.'], 403);
            }
    
            $secretToken = config("secrets.$request->organisation_id");
            $adminToken = config('secrets.admin');
    
            $providedToken = $request->header('secret-token');
    
            if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
                return response()->json(['error' => 'Not authorized.'], 403);
            }
        */

        // === GÉNÉRATION D'UN TOKEN SÉCURISÉ ===
        // Crée une chaîne aléatoire de 60 caractères (cryptographiquement sûre)
        // Cette clé sera stockée en base avec l'expiration et le chemin du document
        $token = Str::random(60);

        // Enregistre le token en base de données avec chemin et date d'expiration
        TokenLinksRapport::generateTokenRapport($token, $path);

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
        $tokenRecord = TokenLinksRapport::where('token', $token)->first();

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

        $tokenEntry = TokenLinksRapport::where('paths', 'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json')->first();

        return response()->json(['message' => 'token valide', 'token' => $tokenEntry->token,]);
    }

}
