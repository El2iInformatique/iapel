<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\TokenLinksRapport;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


/*
 *
 * generateDevis est la fonction pour créer les données et les insérer dans la base de données pour les devis
 * generateOther est la fonction pour créer les données et les insérer dans la base de données pour les rapports, cerfa
 * 
 * Pour les 2 fonctions, un token du même type est créé
 * 
 * Information stockée dans un fichier JSON
 * Token stocké dans la base de données. Le token relie une route avec un token à un devis / rapport / cerfa
 * 
*/


class TokenController extends Controller
{

    // Generation du token pour les devis
    public function generateDevis(Request $request)
    {
        // Validation des données reçues
        /*
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
        ]);*/

        Log::info('Début de la génération du token, devis', ['request_data' => $request->all()]);

        // Récuperation des données
        $data = $request->input();

        $uid = $data['devis_id'];
        $document = "devis";
        $client = $data['organisation_id'];
        $folderPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid);
        $coords = json_decode($data['coords'], true);
        Log::info("Folder : " . $folderPath);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in coords'], 400);
        }


        // Vérifier si le dossier existe, sinon le créer
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

        $jsonPath = $folderPath . '/' . $uid . '.json';
        $dataToken = [
            'client' => $client,
            'document' => $document,
            'uid' => $uid
        ];
        
    
        try {
            // Créer le json
            $jsonData = [
                'dataToken' => $dataToken,
                'organisation_id' => $data["organisation_id"],
                'devis_id' => $data["devis_id"],
                'tiers' => $data["tiers"],
                'client_email' => $data["client_email"],
                'titre' => $data["titre"],
                'montant_HT' => $data["montant_HT"],
                'montant_TVA' => $data["montant_TVA"],
                'montant_TTC' => $data["montant_TTC"],
                $coords,
                'nb_pages' => $data["nb_pages"],
            ];
            
            // Créer le token et l'insérer dans la table
            $token = Str::random(60);

            $retourGenerate = Token::generateToken(
                $token,
                'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json',
                $document,
                false,
            );

            // Test si le token a été créé
            if (!$retourGenerate) {
                return response()->json([
                    'error' => 'Token non généré',
                    500
                ]);
            }

            // Insere les données json dans le json
            file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));

            // Retour car réussite
            return response()->json([
                'message' => 'Token généré avec succès',
                'token' => $token,
                'signature_url' => url('/devis/signature/' . $retourGenerate->token),
            ], 200);

        // En cas d'erreur
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du token : ' . $e->getMessage());
            return response()->json([
                "message" => "erreur lors de la génération du token", 
                500
            ]);
        }
    }


    public function generateOther(Request $request)
    {
        // Validation des données reçues
        /*
        $request->validate([
            'code_client' => 'required|string',
            'nom_client' => 'required|string',
            'email_client' => 'required|string',
            'telephone_client' => 'required|string',
            'portable_client' => 'required|string',
            'adresse_facturation' => 'required|string',
            'cp_facturation' => 'required|string',
            'ville_facturation' => 'required|string',
            'lieu_intervention' => 'required|string',
            'adresse_intervention' => 'required|string',
            'cp_intervention' => 'required|string',
            'ville_intervention' => 'required|string',
            'intervenant' => 'required|string',
            'description' => 'required|string',
        ]);*/

        Log::info('Début de la génération du token, rapport / cerfa', ['request_data' => $request->all()]);

        $data = $request->input();

        $uid = $data['uid'];
        $document = $data['document'];
        $client = $data['client'];
        $folderPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid);

        // Vérifier si le dossier existe, sinon le créer
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

        $jsonPath = $folderPath . '/' . $uid . '.json';
        $dataToken = [
            'client' => $client,
            'document' => $document,
            'uid' => $uid
        ];

        try {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }

            if (str_starts_with($document, 'cerfa_15497')) {

              $jsonData = [
                  'dataToken' => $dataToken,
                    'operateur' => $data['Operateur'] ?? '',
                    'detenteur' => $data['Detenteur'] ?? ''
                ];

            } else if ($document == 'cerfa_13948-03') {

              $jsonData = [
                  'dataToken' => $dataToken,
                    'nom' => $data['nom'] ?? '',
                    'prenom' => $data['prenom'] ?? '',
                    'adresse' => $data['adresse'] ?? '',
                    'commune' => $data['commune'] ?? '',
                    'code_postal' => $data['code_postal'] ?? ''
                ];
            } else if ($document == 'rapport_intervention') {

                $jsonData = [
                    'dataToken' => $dataToken,
                    'code_client' => $data['code_client'] ?? '',
                    'nom_client' => $data['nom_client'] ?? '',
                    'email_client' => $data['email_client'] ?? '',
                    'telephone_client' => $data['telephone_client'] ?? '',
                    'portable_client' => $data['portable_client'] ?? '',
                    'adresse_facturation' => $data['adresse_facturation'] ?? '',
                    'cp_facturation' => $data['cp_facturation'] ?? '',
                    'ville_facturation' => $data['ville_facturation'] ?? '',
                    'lieu_intervention' => $data['lieu_intervention'] ?? '',
                    'adresse_intervention' => $data['adresse_intervention'] ?? '',
                    'cp_intervention' => $data['cp_intervention'] ?? '',
                    'ville_intervention' => $data['ville_intervention'] ?? '',
                    'intervenant' => $data['intervenant'] ?? '',
                    'description' => $data['description'] ?? ''
                ];
            }

            $token = Str::random(60);

            $retourGenerate = Token::generateToken(
                $token,
                 'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json',
                 $document,
            );

            if (!$retourGenerate) {
                return response()->json([
                    'error' => 'Token non généré',
                ]);
            }

            file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));

            return response()->json([
                'message' => 'Token généré avec succès',
                'token' => $token,
                'bi_url' => url('/rapport-cerfa/bi/' . $token),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du token : ' . $e->getMessage());
            return response()->json([
                "message" => "erreur lors de la génération du token", 
                500
            ]);
        }
    }

    public static function validateToken($token)
    {
        $tokenRecord = Token::where('token', $token)->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Token invalide ou expiré'], Response::HTTP_FORBIDDEN);
        }

        /*
        $expiresAt = Carbon::parse($tokenRecord->expires_at);

        if ($expiresAt->lessThan(now())) {
            Log::info('Token invalide, date dépassée');

            $tokenRecord->delete();
            return response()->json(['message' => 'Token invalide, date dépassée']);
        }*/

        return response()->json(['message' => 'Valide', 'token' => $tokenRecord->token], 200);
    }


    public function getToken(Request $request, $client, $document, $uid){

        $tokenEntry = Token::where('paths', 'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json')->first();

        return response()->json(['message' => 'token valide', 'token' => $tokenEntry->token,]);
    }

}
