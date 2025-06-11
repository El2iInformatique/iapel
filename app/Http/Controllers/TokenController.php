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

    public function generate(Request $request)
    {
        $request->validate([
            'organisation_id' => 'required|string',
            'devis_id' => 'required|string',
            'tiers' => 'required|string',
            'client_email' => 'required|string',
            'titre' => 'required|string',
            'montant_HT' => 'required|numeric|min:0',
            'montant_TVA' => 'required|numeric|min:0',
            'montant_TTC' => 'required|numeric|min:0',
            'coords' => 'required|json',
            'nb_pages' => 'required|numeric|min:0',
        ]);

        if (!$request->hasHeader('secret-token')) {
            return response()->json(['error' => 'No secret token provided.'], 403);
        }

        $secretToken = config("secrets.$request->organisation_id");
        $adminToken = config('secrets.admin');

        $providedToken = $request->header('secret-token');

        if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        Log::info('Début de la génération du token', ['request_data' => $request->all()]);

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


    public static function generateTokenRapport(Request $request, $path){

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
    
            Log::info('Début de la génération du token', ['request_data' => $request->all()]);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
        */


        $token = Str::random(60);

        TokenLinksRapport::generateTokenRapport($token, $path);

        return $token;
    }

    public static function isValideTokenRapport($token)
    {
        $tokenRecord = TokenLinksRapport::where('token', $token)->first();

        if (!$tokenRecord) {
            return false;
        }

        $expiresAt = Carbon::parse($tokenRecord->expires_at);

        if ($expiresAt->lessThan(now())) {
            Log::info('Token invalide : date depasser');

            $tokenRecord->delete();
            return false;
        }

        return true;
    }
}
