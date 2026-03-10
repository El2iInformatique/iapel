<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TokenController;
use App\Http\Controllers\ClientController;
use App\Models\TokenLinksRapport;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @class BiController
 * @brief Contrôleur principal de gestion des documents et rapports d’intervention.
 *
 * Cette classe gère :
 * - La création, lecture, et suppression des fichiers JSON liés aux documents.
 * - La génération et vérification des tokens.
 * - Le téléchargement et la soumission des rapports d’intervention.
 * - La liste et le statut des documents sauvegardés.
 *
 * @package App\Http\Controllers
 * @version 1.0
 */
class BiController extends Controller
{
     /**
     * @brief Récupère la liste des documents d’un client.
     *
     * Cette méthode lit le fichier JSON correspondant au client spécifié
     * et renvoie la liste de ses documents.
     *
     * @param string $client Nom du client.
     * @return JsonResponse Liste des documents ou message d’erreur.
     */
    public function getDocuments($client)
    {
        // Récupération du fichier JSON
        $filePath = storage_path('app/public/' . $client . '/documents.json');

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            \Log::error("[DOCUMENT] FICHIER JSON INTROUVABLE", [
                'client' => $client,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        // Lire et décoder le JSON
        $json = file_get_contents($filePath);
        $data = json_decode($json, true); // true => convertir en tableau associatif

        return response()->json($data);
    }


    public function checkExistPdf($jsonPath, $dataToken): bool {
        $pdfFile = str_replace('.json', '.pdf', $jsonPath) 
        ?? storage_path("app/public/{$dataToken['client']}/{$dataToken['document']}/{$dataToken['uid']}/{$dataToken['uid']}.pdf");

        $alreadyExists = !empty($dataToken['pdf_exists']) || file_exists($pdfFile);

        return $alreadyExists;
    }

    /**
     * @brief Crée un fichier JSON et un token associé pour un document client.
     *
     * Génère un identifiant unique (UID), crée la structure du document,
     * et associe un token pour l’accès sécurisé à ce document.
     *
     * @param Request $request Requête HTTP contenant les données du document.
     * @return JsonResponse Contient le token et l’URL d’accès BI.
     */
    // fonction de création du JSON et du TOKEN d'identification du fichier
    public function createJson(Request $request)
    {
        // Définition des règles de base
        $baseRules = [
            'document' => 'required|string|in:cerfa_15497,rapport_intervention',
            'client'   => 'required|string',
            'uid'      => 'required|string|max:50',
        ];

        // Détermination des règles spécifiques selon le type de document
        $specificRules = [];
        $documentType = $request->input('document');

        if ($documentType === 'cerfa_15497') {
            $specificRules = [
                'operateur' => 'nullable|string',
                'detenteur' => 'nullable|string',
            ];
        } elseif ($documentType === 'rapport_intervention') {
            $specificRules = [
                'code_client'          => 'nullable|string',
                'nom_client'           => 'nullable|string',
                'email_client'         => 'nullable|email',
                'telephone_client'     => 'nullable|string',
                'portable_client'      => 'nullable|string',
                'adresse_facturation'  => 'nullable|string',
                'cp_facturation'       => 'nullable|string',
                'ville_facturation'    => 'nullable|string',
                'date_intervention'    => 'nullable|date',
                'adresse_intervention' => 'nullable|string',
                'lieu_intervention'    => 'nullable|string',
                'cp_intervention'      => 'nullable|string',
                'ville_intervention'   => 'nullable|string',
                'intervenant'          => 'nullable|string',
                'description'          => 'nullable|string',
            ];
        }

        // Initialisation avec des valeurs par défaut pour sécuriser les logs du bloc catch
        $client = $request->input('client', 'inconnu');
        $uid    = $request->input('uid', 'inconnu');

        try {
            $validator = Validator::make($request->all(), array_merge($baseRules, $specificRules));

            if ($validator->fails()) {
                Log::warning("[VALIDATION] Echec de validation des données d'entrée", [
                    'client' => $client,
                    'uid' => $uid,
                    'errors' => $validator->errors()->toArray(),
                    'fonction' => __FUNCTION__,
                    'fichier' => basename(__FILE__),
                    'ligne' => __LINE__
                ]);
                return response()->json([
                    'error'   => 'Validation échouée',
                    'details' => $validator->errors()
                ], 422); 
            }

            $validated = $validator->validated();
            
            // Mise à jour avec les données validées et sûres
            $uid      = $validated['uid'];
            $document = $validated['document'];
            $client   = $validated['client'];

            $succes = ClientController::create($client, $document, $uid, $validated);

            if (!$succes) {
                throw new Exception("Erreur lors de la création du nouveau document");
            }

            // Préparation du chemin et des données
            $relativeFolder = "{$client}/{$document}/{$uid}";
            $relativeFilePath = "{$relativeFolder}/{$uid}.json";

            // Génération du Token
            $fullPathForToken = "app/public/{$relativeFilePath}";
            $token = TokenController::generateTokenRapport($request, $fullPathForToken);


            return response()->json([
                'message' => 'Document créé avec succès',
                'token'   => $token,
                'bi_url'  => url("/bi/{$token}"),
            ], 201);

        } catch (Exception $e) {
            Log::error("[JSON_CREATION_FAILED] Client: {$client}, UID: {$uid}", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Une erreur technique est survenue lors de la génération du document.'
            ], 500);
        }
    }
    
    /**
     * @brief Télécharge le fichier PDF correspondant à un token donné.
     *
     * Permet à l’utilisateur de récupérer le document PDF associé
     * à un token d’accès valide.
     *
     * @param string $token Token du document.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Fichier PDF téléchargé.
     */
    public function download($token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            \Log::error("[TELECHARGEMENT] FICHIER JSON INTROUVABLE", [
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            abort(404, 'Fichier introuvable');
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        $filePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        if (!file_exists($filePath)) {
            \Log::error("[TELECHARGEMENT] FICHIER PDF INTROUVABLE", [
                'client' => $client,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        return response()->download($filePath, "{$uid}.pdf");
    }

    /**
     * @brief Vérifie la présence des fichiers JSON et PDF d’un document.
     *
     * Vérifie si un document (identifié par client, document, UID)
     * possède les fichiers nécessaires dans le stockage.
     *
     * @param string $client Nom du client.
     * @param string $document Nom du document.
     * @param string $uid Identifiant unique.
     * @param Request $request Requête contenant le token secret d’accès.
     * @return JsonResponse Statut de la présence des fichiers.
     */
    public function check($client, $document, $uid, Request $request)
    {
        $jsonPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json');
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        return response()->json([
            'json_exists' => file_exists($jsonPath),
            'pdf_exists' => file_exists($pdfPath)
        ]);
    }

    /**
     * @brief Affiche la vue HTML d’un document en fonction de son token.
     *
     * Charge et affiche la vue correspondant au document stocké
     * à partir des données associées au token fourni.
     *
     * @param string $token Token du document.
     * @return \Illuminate\View\View Vue HTML du document.
     */
    public function show($token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        // Vérifier si le token existe - si le token n'existe pas, on affiche une page d'erreur 404
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__
            ]);
            abort(404, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            \Log::error("[JSON] FICHIER JSON INTROUVABLE", [
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            abort(404, 'Fichier introuvable');
        }

        if ($this->checkExistPdf($filePath, $dataToken)) {
            $client = $dataToken['client'] ?? 'unknown_client';
            \Log::warning("[JSON] ACCES REFUSER", [
                'client' => $client,
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            abort(403, 'Bon d\'intervention déjà généré.', ['Content-Type' => 'text/html']);
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        if (str_starts_with($document, 'cerfa_15497_')) {
            return view('cerfa_15497', compact('data', 'token', 'uid', 'client', 'document'));
        }
        else if (str_starts_with($document, 'cerfa')) {
            return view($document, compact('data', 'token', 'uid', 'client', 'document'));
        } else {

        $allOption = ClientController::getOptionsBI($client);
        
        $optionsConstat = [];
        $optionsVerification = [];
        $optionsNotesParticuliere = [];
        $optionsPointVigilance = [];

        // On vérifie qu'on a bien reçu les 4 tableaux attendus
        if (is_array($allOption) && count($allOption) >= 4) {
            $optionsConstat           = $allOption[0];
            $optionsVerification      = $allOption[1];
            $optionsNotesParticuliere = $allOption[2];
            $optionsPointVigilance    = $allOption[3];
        }

        // IMPORTANT : Ajoute toutes les options dans le compact pour les utiliser en front
        return view('bi', compact(
            'data', 
            'token', 
            'uid', 
            'client', 
            'document', 
            'optionsConstat',
            'optionsVerification', 
            'optionsNotesParticuliere', 
            'optionsPointVigilance'
        ));

        }
    }

    /**
     * @brief Soumet un rapport d’intervention au serveur.
     *
     * Enregistre le fichier PDF envoyé par le front-end, le lie
     * au token correspondant et met à jour le statut du document.
     *
     * @param Request $request Contient les fichiers et les métadonnées du rapport.
     * @return \Illuminate\Http\RedirectResponse Résultat de la soumission.
     */
    public function submit(Request $request, $token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        if (!$dataToken) {
            abort(404, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        // Construire le chemin du fichier JSON
        $filePath = storage_path($dataToken['paths']);

        // Vérifier si le fichier JSON existe
        if (!file_exists($filePath)) {
            \Log::error("[JSON] TOKEN INTROUVABLE", [
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            abort(404, "Fichier introuvable");
        }

        // Vérifier si le PDF existe déjà (pour éviter les doublons)
        if ($this->checkExistPdf($filePath, $dataToken)) {
            $client = $dataToken['client'] ?? 'unknown_client';
            \Log::error("[REFUSE] SOUMISSION REFUSER", [
                'client' => $client,
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $filePath
            ]);
            abort(403, 'Bon d\'intervention déjà généré.', ['Content-Type' => 'text/html']);
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        // On délègue le traitement des requêtes et l'enregistrement au ClientController
        $succes = ClientController::store($client, $document, $uid, $request, $data);

        if (!$succes) {
            abort(500, "Une erreur est survenue lors de l'enregistrement des données.");
        }

        // Rediriger avec un message de succès
        return redirect()->route('pdf.view', ['token' => $token]);
    }


    /**
     * @brief Liste les documents sauvegardés pour un client.
     *
     * Retourne la liste des rapports et documents disponibles
     * dans le répertoire du client spécifié.
     *
     * @param string $entreprise Nom du client.
     * @param Request $request
     * @return JsonResponse Liste des fichiers enregistrés.
     */
    public function listSavedDocs($entreprise, Request $request): JsonResponse
    {
        try {
            // Le secret token est déjà vérifié dans le middleware VerifSecretToken

            \Log::info("[ACCES] ACCES AUTORISER - listSavedDocs", [
                'client' => $entreprise,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
            ]);

            // Appel de la logique métier déléguée au ClientController
            $documents = ClientController::getAllDocuments($entreprise);

            return response()->json($documents);

        } catch (\Exception $e) {
            \Log::error("[CATCH] ERREUR INCONNUE", [
                'client' => $entreprise,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Une erreur est survenue: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @brief Supprime un document spécifique.
     *
     * @param Request $request
     * @param string $token Le token identifiant le document.
     * @return JsonResponse
     */
    public function delete(Request $request, $token): JsonResponse
    {
        // 1. Récupération du lien via le token
        $dataToken = TokenLinksRapport::where('token', $token)->first();

        if (!$dataToken) {
            return response()->json(['error' => 'Lien vers le rapport introuvable.'], 404);
        }

        // Récupération du chemin du fichier JSON pour extraction des infos
        // Note : On utilise storage_path car 'paths' en base contient souvent 'app/public/...'
        $filePath = storage_path($dataToken->paths);

        if (!file_exists($filePath)) {
            // Si le fichier n'existe plus, on nettoie quand même la DB et on informe
            $dataToken->delete();
            return response()->json(['error' => 'Fichier JSON déjà absent du serveur. Nettoyage DB effectué.'], 404);
        }

        try {
            // Lire le JSON pour récupérer la structure client/document/uid
            $content = file_get_contents($filePath);
            $jsonData = json_decode($content, true);
            
            $client   = $jsonData['dataToken']['client'] ?? null;
            $document = $jsonData['dataToken']['document'] ?? null;
            $uid      = $jsonData['dataToken']['uid'] ?? null;

            if (!$client || !$document || !$uid) {
                throw new \Exception("Structure JSON invalide pour la suppression.");
            }

            // Appel au ClientController pour la suppression physique et DB
            $isDeleted = ClientController::delete($client, $document, $uid, $dataToken);

            if ($isDeleted) {
                return response()->json(['status' => 'Success.'], 200);
            } else {
                return response()->json(['error' => 'Erreur lors de la suppression des fichiers.'], 500);
            }

        } catch (\Throwable $th) {
            \Log::error("[BI_CONTROLLER] Erreur delete", ['msg' => $th->getMessage()]);
            return response()->json(['error' => 'Erreur interne lors de la suppression.'], 500);
        }
    }
}
