<?php

namespace App\Http\Controllers;

use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Models\TokenLinks;
use App\Services\JsonReader;


/**
 * @class DevisController
 * @brief Contrôleur responsable de la gestion et de la suppression des devis signés.
 *
 * Ce contrôleur permet :
 * - De vérifier l’existence d’un devis (PDF ou version certifiée).
 * - De supprimer un devis ainsi que son token associé de manière sécurisée.
 * - De protéger les accès via un système de jeton secret d’organisation.
 *
 * @package App\Http\Controllers
 * @version 1.0
 */
class DevisController extends Controller
{
     /**
     * @brief Supprime un devis et les fichiers associés à partir d’un token.
     *
     * Cette méthode :
     * - Valide le token fourni dans l’URL.
     * - Vérifie le secret token d’autorisation (client ou administrateur).
     * - Supprime le fichier PDF du devis, le PDF certifié, le répertoire du devis
     *   ainsi que le token associé dans la base de données.
     *
     * @param Request $request Requête HTTP contenant le header `secret-token`.
     * @param string $noToken Token public du devis à supprimer.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l’échec de l’opération.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException Si le token n’est pas valide ou si l’utilisateur n’est pas autorisé.
     * @throws \Exception En cas d’erreur lors de la suppression des fichiers.
     *
     * @note Cette opération est irréversible. Les fichiers supprimés ne peuvent pas être restaurés.
     */

    public function createJson(Request $request) 
    {
        // Règles de validation conservées à l'identique
        $rules = [
            'organisation_id' => 'required|string',  // Identifiant du prestataire
            'devis_id'        => 'required|string',  // Numéro unique du devis
            'tiers'           => 'required|string',
            'client_email'    => 'required|string',
            'titre'           => 'required|string',
            'montant_HT'      => 'required|numeric|min:0',
            'montant_TVA'     => 'required|numeric|min:0',
            'montant_TTC'     => 'required|numeric|min:0',
            'coords'          => 'required|json',
            'nb_pages'        => 'required|numeric|min:0',
        ];

        // Initialisation avec des valeurs par défaut pour sécuriser les logs du bloc catch
        $organisation_id = $request->input('organisation_id', 'inconnu');
        $devis_id        = $request->input('devis_id', 'inconnu');

        try {
            // Création du validateur Laravel
            $validator = Validator::make($request->all(), $rules);

            // Si la validation échoue, on log et on retourne les erreurs
            if ($validator->fails()) {
                \Log::warning("[VALIDATION] Echec de validation des données d'entrée", [
                    'organisation_id' => $organisation_id,
                    'devis_id'        => $devis_id,
                    'errors'          => $validator->errors()->toArray(),
                    'fonction'        => __FUNCTION__,
                    'fichier'         => basename(__FILE__),
                    'ligne'           => __LINE__
                ]);

                return response()->json([
                    'error'   => 'Validation échouée',
                    'details' => $validator->errors()
                ], 422); 
            }

            $relativeFolder = "{$organisation_id}/devis/{$devis_id}";
            $pdfCertifiePath = "{$relativeFolder}/{$devis_id}_certifie.pdf";
            // Préparation du chemin relatif vers le fichier JSON
            $relativeFilePath = "{$relativeFolder}/{$devis_id}.json";

            // Génération du Token
            $fullPathForToken = "app/public/{$relativeFilePath}";

            // Si le PDF existe, on affiche la vue de téléchargement
            if (Storage::disk('public')->exists($pdfCertifiePath)) {
                \Log::warning("[CREATE] TENTATIVE CREATION DEVIS SUR UN DEViS DEJA EXISTANT ET CERTIFIE", [
                    'client' => $organisation_id,
                    'validator' => $validator,
                    'chemin' => $fullPathForToken
                ]);

                return response()->json([
                    'error' => 'Token déjà généré'
                ], 403);
            }


            if (Storage::disk('public')->exists($relativeFolder)) {
                \Log::info("Full path token : " . $fullPathForToken);

                $token = TokenLinks::where('paths', '=', $fullPathForToken)->first()->token;

                \Log::warning("[DELETE] SUPPRESSION DEVIS DUPLICATA NON CERTIFIE", [
                    'client' => $organisation_id,
                    'token' => $token,
                    'chemin' => $relativeFolder
                ]);

                if ($token) 
                {
                    $json = self::delete($request, $token);
                    if ($json->status() == 500 ) {
                        
                        return response()->json(["error" => "Erreur lors de la suppresion du devis."], 500);
                    }
                }
            }


            // Récupère les données validées
            $validated = $validator->validated();
            
            // On récupère toutes les clés attendues par le validateur
            $expectedKeys = array_keys($rules);
            
            // On crée un tableau avec toutes ces clés initialisées à ""
            $fullData = array_fill_keys($expectedKeys, "");
            
            // On écrase les valeurs par défaut avec les données reçues
            $finalData = array_merge($fullData, $validated);

            // Si une valeur est explicitement nulle, on la remplace par une chaîne vide
            $finalData = array_map(function($value) {
                return $value === null ? "" : $value;
            }, $finalData);
            // ----------------------------------------------------

            \Log::info("[DOCUMENT] DEBUT GENERATION TOKEN", [
                'organisation_id' => $finalData['organisation_id'],
                'devis_id'        => $finalData['devis_id'],
                'request_data'    => $finalData
            ]);

             // Permet en cas de d'excption de la capturer et d'en renvoyer une autre
            $coords = rescue(
                fn() => json_decode($finalData['coords'], true, 512, JSON_THROW_ON_ERROR),
                fn() => throw new \Exception('Les coordonnées JSON sont malformées.')
            );

            // Génération du Token
            $token = TokenController::generateToken($fullPathForToken, 'devis');

            $nom_doc = "{$devis_id}";

            if (!ClientController::createDevis($organisation_id, "devis", $nom_doc, $validated)) 
            {
                // Supprime tout se qui a était créer pour que se soit propre
                if ($token) 
                {
                    $json = self::delete($request, $token);
                    if ($json->status() == 500 ) {
                        
                        throw new ErrorException("Le dossier client n'a pas put étre supprimer !");
                    }
                }

                return response()->json([
                    'error' => 'Une erreur technique est survenue lors de la génération du JSON.'
                ], 500);
            }

            // On passe le code HTTP à 201 (Created) pour harmoniser avec le BiController
            return response()->json([
                'message'       => 'Token généré avec succès',
                'token'         => $token,
                'signature_url' => url('/signature/' . $token),
            ], 201);

        } catch (\Exception $e) {
            \Log::error("[JSON_CREATION_FAILED] Organisation: {$organisation_id}, Devis: {$devis_id}", [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Une erreur technique est survenue lors de la génération du devis.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request, $token): JsonResponse
    {
        \Log::info("[REQUEST] SUPPRESSION DU DEVIS CLIENT DEMANDER", [
                'token'    => $token,
                'ip' => $request->ip(),
                'route' => $request->route(),
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__
            ]);

        // Récupération des infos liées au token
        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__
            ]);
            return response()->json(['error' => 'Token introuvable'], 404);
        }

        // Permet en cas de d'excption de la capturer et de renvoyer une reponse json
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => response()->json(['error' => 'Erreur lors de la récupération des données.'], 404)
        );

        if ($data instanceof JsonResponse) return $data;

        // Extraction des variables de base (priorité au JSON, fallback sur la DB)
        $uid    = $data["dataToken"]['devis_id'] ?? "";
        $client = $data["dataToken"]['organisation_id'] ?? "";

        // === CONSTRUCTION DES CHEMINS DE FICHIERS ===
        $devisPath = "{$client}/devis/{$uid}/";

        try {
            // Système d'archivage des documents certifiés plus tard ici

            if (Storage::disk('public')->exists($devisPath)){
                Storage::disk('public')->deleteDirectory($devisPath);
            }

            // CORRECTION ICI : On supprime l'objet récupéré en base, pas la string
            $dataToken->delete();

            \Log::info("[SUCCES] SUPPRESSION DU DEVIS CLIENT REUSSI", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => storage_path($dataToken->paths)

            ]);

            return response()->json(['success' => 'Devis supprimé avec succès'], 200);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    /**
     * @brief Vérifie la présence d’un devis associé à un token donné.
     *
     * Cette méthode :
     * - Vérifie l’existence d’un token valide.
     * - Contrôle le secret token d’accès pour s’assurer de l’autorisation.
     * - Confirme si un fichier PDF ou PDF certifié existe pour ce devis.
     *
     * @param Request $request Requête HTTP contenant le header `secret-token`.
     * @param string $noToken Token public du devis à vérifier.
     *
     * @return JsonResponse Réponse JSON indiquant si le devis existe.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException Si le token n’est pas valide ou si l’utilisateur n’est pas autorisé.
     * @throws \Exception En cas d’erreur lors de la vérification.
     *
     * @note Cette méthode ne renvoie jamais le contenu du fichier, uniquement son existence.
     */
    public function check(Request $request, $token): JsonResponse {
        // Récupération des infos liées au token
        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__
            ]);
            return response()->json(['exists' => false], 200);
        }

        // Permet en cas de d'excption de la capturer et de renvoyer une reponse json
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => response()->json(['error' => 'Erreur lors de la récupération des données.'], 404)
        );

        if ($data instanceof JsonResponse) return $data;

        // Extraction des variables de base (priorité au JSON, fallback sur la DB)
        $devis_id       = $data["dataToken"]['devis_id'] ?? "";
        $organisation_id    = $data["dataToken"]['organisation_id'] ?? "";

        $pdfPath = $organisation_id . '/devis/' . $devis_id . '/' . $devis_id . '.pdf';
        $pdfPathCertif = $organisation_id . '/devis/' . $devis_id . '/' . $devis_id . '_certifie.pdf';

        try {
            
            $exists = Storage::disk('public')->exists($pdfPath) || Storage::disk('public')->exists($pdfPathCertif);

            return response()->json(['exists' => $exists], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la verification : ' . $e->getMessage());

            return response()->json(['error' => 'Erreur lors de la verification'], 500);
        }
    }

    public function downloadDevis(Request $request, $token) {

        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__
            ]);
            return response()->json(['exists' => false], 404);
        }

        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => response()->json(['error' => 'Erreur lors de la récupération des données.'], 404)
        );

        if ($data instanceof JsonResponse) return $data;

        $client = $data["dataToken"]["organisation_id"];
        $uid = $data["dataToken"]["devis_id"];
        
        $pdfPathCertifie = storage_path("app/public/{$client}/devis/{$uid}/{$uid}_certifie.pdf");
        $filePath = storage_path($dataToken->paths);

        if (!file_exists($filePath)) {
            return response()->json(['exists' => false], 404);
        }

        return response()->download($pdfPathCertifie, "{$uid}.pdf");

    }

    public function refuse(Request $request, $token) {

        \Log::info("Debut refut token");

        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            \Log::error("[DATA_TOKEN] TOKEN INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__
            ]);
            return response()->json(['exists' => false], 404);
        }
        
        // Permet en cas de d'excption de la capturer et de renvoyer une reponse json
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => response()->json(['succes' => false], 400)
        );

        if ($data instanceof JsonResponse) return $data;


        $client = $data["dataToken"]["organisation_id"];
        $devisId = $data["dataToken"]["devis_id"];

        $data["refused"] = true;

        $stored = Storage::disk('public')->put(
            "/$client/devis/$devisId/$devisId.json", 
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        \Log::info("Devis refuser !");

        // On s'assure que le fichier a bien été écrit sur le disque
        if (!$stored) {
            throw new \Exception("Échec de l'écriture du fichier JSON sur le disque.");
        }

        \Log::info("Renvoye reponse !");
        return response()->json(["succes" => true], 200);
    }
}
