<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TokenController; // Contrôleur pour gérer les tokens de rapport
use App\Http\Controllers\ClientController; // Contrôleur pour gérer les documents clients
use App\Models\TokenLinks; // Modèle de liaison entre token et rapport

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

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

     public function formatDate($date): ?string 
    {
        // Si la date est vide, nulle ou n'est pas une chaîne exploitable
        if (empty($date)) {
            return null;
        }

        // Nettoyage des espaces en début/fin
        $date = trim($date);

        // Uniformisation des séparateurs de date en "/" pour Carbon
        $date = str_replace(['/', '-', '.'], '/', $date);

        try {
            // Tenter de parser et formater avec Carbon
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            // En cas d'erreur de parsing, logguer et renvoyer null
            Log::error("Format de date invalide : " . $date);
            return null;
        }
    }
    
    /**
     * Récupère la liste des documents disponibles pour un client donné.
     *
     * Le fichier 'documents.json' est stocké par client dans le stockage public.
     * Si le fichier n'existe pas, une erreur 404 est renvoyée.
     *
     * @param string $client Nom du client.
     * @return JsonResponse Liste des documents ou message d'erreur.
     */
    public function getDocuments($client)
    {
        // Construction du chemin vers le fichier JSON du client
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

        // Lire le contenu du fichier JSON
        $json = file_get_contents($filePath);

        // Décoder le JSON en tableau associatif
        $data = json_decode($json, true); // true => convertir en tableau associatif

        // Retourner les données au format JSON
        return response()->json($data);
    }


    /**
     * Indique si un PDF a déjà été généré pour un rapport donné.
     *
     * Cette vérification utilise deux sources :
     * - le champ 'pdf_exists' présent dans les métadonnées de token,
     * - la présence physique du fichier PDF dans le stockage.
     *
     * @param string $jsonPath Chemin absolu vers le fichier JSON existant.
     * @param array $dataToken Données issues de la table TokenLinks.
     * @return bool true si le PDF existe déjà, false sinon.
     */
    public function checkExistPdf($jsonPath, $dataToken): bool {
        // Détermine le chemin du fichier PDF à partir du chemin JSON (ou fallback)
        $pdfFile = str_replace('.json', '.pdf', $jsonPath) 
        ?? storage_path("app/public/{$dataToken['client']}/{$dataToken['document']}/{$dataToken['uid']}/{$dataToken['uid']}.pdf");

        // Vérifier si le PDF existe déjà (dans la DB ou sur le disque)
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
    public function createJson(Request $request)
    {
        // Définition des règles de validation minimales
        $baseRules = [
            'document' => 'required|string|in:cerfa_15497,rapport_intervention',
            'client'   => 'required|string',
            'uid'      => 'required|string|max:50',
        ];

        // Prépare les règles spécifiques selon le type de document
        $specificRules = [];
        $documentType = $request->input('document');

        if ($documentType === 'cerfa_15497') {
            $specificRules = [
                'operateur' => 'nullable|string',
                'detenteur' => 'nullable|string',
                'numero_attestation_capacite' => 'nullable|string',
                'identification' => 'nullable|string',
                'denomination' => 'nullable|string',
                'charge' => 'nullable|string',
                'tonnage' => 'nullable|string',
                'identification_controle' => 'nullable|string',
                'date_controle' => 'nullable|date',
                'detection_fuites' => 'nullable|string',
                'constat_fuites' => 'nullable|string',
                'localisation_fuite_1' => 'nullable|string',
                'localisation_fuite_2' => 'nullable|string',
                'localisation_fuite_3' => 'nullable|string',
                'quantite_chargee_totale' => 'nullable|string',
                'quantite_chargee_A' => 'nullable|string',
                'fluide_A' => 'nullable|string',
                'quantite_chargee_B' => 'nullable|string',
                'quantite_chargee_C' => 'nullable|string',
                'quantite_recuperee_totale' => 'nullable|string',
                'quantite_recuperee_D' => 'nullable|string',
                'BSFF' => 'nullable|string',
                'quantite_recuperee_E' => 'nullable|string',
                'identification_E' => 'nullable|string',
                'autre_fluide_non_inflammable' => 'nullable|string',
                'autre_fluide_inflammable' => 'nullable|string',
                'installation_destination_fluide' => 'nullable|string',
                'observations' => 'nullable|string',
                'nom_signataire_operateur' => 'nullable|string',
                'qualite_signataire_operateur' => 'nullable|string',
                'nom_signataire_detenteur' => 'nullable|string',
                'qualite_signataire_detenteur' => 'nullable|string',
                'date_signature_operateur' => 'nullable|string',
                'signature-operateur' => 'nullable|string',
                'date_signature_detenteur' => 'nullable|string',
                'signature-detenteur' => 'nullable|string',
            ];
        } elseif ($documentType === 'rapport_intervention') {
            $specificRules = [
                'code_client'          => 'nullable|string',
                'equipier'             => 'nullable|string',
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

        // Fusion de toutes les règles de validation
        $allRules = array_merge($baseRules, $specificRules);

        // Initialisation avec des valeurs par défaut pour sécuriser les logs du bloc catch
        $client = $request->input('client', 'inconnu');
        $uid    = $request->input('uid', 'inconnu');

        try {
            // Création du validateur Laravel
            $validator = Validator::make($request->all(), $allRules);

            // Si la validation échoue, on log et on retourne les erreurs
            if ($validator->fails()) {
                Log::warning("[VALIDATION] Echec de validation des données d'entrée", [
                    'client'   => $client,
                    'uid'      => $uid,
                    'errors'   => $validator->errors()->toArray(),
                    'fonction' => __FUNCTION__,
                    'fichier'  => basename(__FILE__),
                    'ligne'    => __LINE__
                ]);
                return response()->json([
                    'error'   => 'Validation échouée',
                    'details' => $validator->errors()
                ], 422); 
            }

            // Récupère les données validées
            $validated = $validator->validated();
            
            // On récupère toutes les clés attendues par le validateur
            $expectedKeys = array_keys($allRules);
            
            // On crée un tableau avec toutes ces clés initialisées à ""
            $fullData = array_fill_keys($expectedKeys, "");
            
            // On écrase les valeurs par défaut avec les données reçues
            $finalData = array_merge($fullData, $validated);

            // Si une valeur est explicitement nulle, on la remplace par une chaîne vide
            $finalData = array_map(function($value) {
                return $value === null ? "" : $value;
            }, $finalData);
            // ----------------------------------------------------

            // Mise à jour des variables critiques avec les données validées/enrichies
            $uid      = $finalData['uid'];
            $document = $finalData['document'];
            $client   = $finalData['client'];

            // On appelle le contrôleur client pour créer le document côté stockage
            $succes = ClientController::create($client, $document, $uid, $finalData);

            if (!$succes) {
                throw new Exception("Erreur lors de la création du nouveau document");
            }

            // Préparation du chemin relatif vers le fichier JSON
            $relativeFolder = "{$client}/{$document}/{$uid}";
            $relativeFilePath = "{$relativeFolder}/{$uid}.json";

            // Génération du Token
            $fullPathForToken = "app/public/{$relativeFilePath}";
            $token = TokenController::generateToken($fullPathForToken, $document); // requete + path + type document

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
        // Recherche du token en base pour retrouver le chemin du JSON associé
        $dataToken = TokenLinks::where('token', $token)->get()->first();
        
        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );

        // Extraire les éléments nécessaires depuis le JSON
        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        // Chemin du PDF correspondant
        $filePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        // Vérifier l'existence du PDF
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

        // Retourner le PDF en téléchargement
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
        // Construction des chemins attendus pour JSON et PDF
        $jsonPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json');
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        // Retourner le résultat des vérifications d'existence
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
        // Récupération des infos liées au token
        $dataToken = TokenLinks::where('token', $token)->get()->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token' => $token,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__
            ]);
            abort(404, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        // Construire le chemin du fichier JSON à partir du token
        $filePath = storage_path($dataToken['paths']);

        // Vérifier que le JSON existe
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

        // Si un PDF existe déjà, on refuse l'accès pour éviter les doublons
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

        // lire et décoder le JSON pour construire la vue
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        // Si on a un cerfa spécifique, on utilise sa vue dédiée
        if (str_starts_with($document, 'cerfa_15497_')) {
            return view('cerfa_15497', compact('data', 'token', 'uid', 'client', 'document'));
        }
        // Si le document commence par cerfa, on affiche la vue correspondante
        else if (str_starts_with($document, 'cerfa')) {
            return view($document, compact('data', 'token', 'uid', 'client', 'document'));
        } else {

            // Si la date d'intervention est présente, on la formate
            if (isset($data["date_intervention"])) {
                $data["date_intervention"] = $this->formatDate($data["date_intervention"]) ?? null;
            }

            // Récupération des options BI pour le client
            $allOption = ClientController::getOptionsBI($client);
            
            // Initialisation des tableaux d'options
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
        // Récupération du token en DB
        $dataToken = TokenLinks::where('token', $token)->get()->first();
        
        try {
            // Permet en cas de d'excption de la capturer et d'utiliser abort()
            $data = rescue(
                fn() => JsonReader::fromToken($dataToken, __CLASS__),
                fn() => abort(500, "Erreur lors de la récupération de vos données.")
            );
            
            ['client' => $client, 'document' => $document, 'uid' => $uid] = $data["dataToken"];

        // Si token introuvable, bloquer
        if (!$dataToken) {
            abort(404, 'Accès refusé | Lien vers le rapport d\'intervention introuvable.', ['Content-Type' => 'text/html']);
        }

        // Construire le chemin du JSON associé
        $filePath = storage_path($dataToken['paths']);

        // Vérifier que le fichier JSON existe
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

        // Vérifier si un PDF existe déjà pour éviter les doublons
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

        // Lecture du JSON existant pour récupérer les métadonnées
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        // Délégation au ClientController pour stocker le fichier PDF et mettre à jour les données
        $succes = ClientController::store($client, $document, $uid, $request, $data);

        // En cas d'échec, on remonte une erreur
        if (!$succes) {
            abort(500, "Une erreur est survenue lors de l'enregistrement des données.");
        }

        // Rediriger vers la vue PDF finale
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

            // Retourner la liste des documents
            return response()->json($documents);

        } catch (\Exception $e) {
            // En cas d'erreur, on log et on retourne un message générique
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
        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on renvoie 404
        if (!$dataToken) {
            return response()->json(['error' => 'Lien vers le rapport introuvable.'], 404);
        }
        
        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $jsonData = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );

        try {
            // Lire le JSON pour récupérer le contenu
            $content = file_get_contents($filePath);
            $jsonData = json_decode($content, true);
            
            $client   = $jsonData['dataToken']['client'] ?? null;
            $document = $jsonData['dataToken']['document'] ?? null;
            $uid      = $jsonData['dataToken']['uid'] ?? null;

            if (!$client || !$document || !$uid) {
                throw new \Exception("Structure JSON invalide pour la suppression.");
            }

            // Appel au ClientController pour la suppression physique et en base
            $isDeleted = ClientController::delete($client, $document, $uid, $dataToken);

            if ($isDeleted) {
                return response()->json(['status' => 'Success.'], 200);
            } else {
                return response()->json(['error' => 'Erreur lors de la suppression des fichiers.'], 500);
            }

        } catch (\Throwable $th) {
            // Log en cas d'erreur inattendue
            \Log::error("[BI_CONTROLLER] Erreur delete", ['msg' => $th->getMessage()]);
            return response()->json(['error' => 'Erreur interne lors de la suppression.'], 500);
        }
    }
}
