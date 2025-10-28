<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

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
    public function delete(Request $request, $noToken): JsonResponse
    {
        $token = Token::where('token', $noToken)->first();

        if (!$token) {
            return response()->json(['error' => 'Token non trouvé'], 404);
        }

        if(!$request->hasHeader('secret-token')){
            return response()->json(['error'=>'No secret token provided.'], 403);  
        }
        
        $secretToken = config("secrets.$token->organisation_id"); 
        $adminToken = config('secrets.admin'); 

        $providedToken = $request->header('secret-token');

        if(!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)){
            return response()->json(['error'=>'Not authorized.'], 403);
        }

        $devisDir = $token->devis_id . '_' . $noToken;

	    $devisPath = $token->organisation_id . '/devis/' . $devisDir . '/';
        $pdfPath = $token->organisation_id . '/devis/' . $devisDir . '/' . $devisDir . '.pdf';
        $pdfPathCertif = $token->organisation_id . '/devis/' . $devisDir . '/' . $devisDir . '_certifie.pdf';
        try {
            // Supprimer le(s) fichier(s) PDF s'il(s) existe(nt)
            if (Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }

            if (Storage::disk('public')->exists($pdfPathCertif)) {
                Storage::disk('public')->delete($pdfPathCertif);
            }

	    if (Storage::disk('public')->exists($devisPath)){
		Storage::disk('public')->deleteDirectory($devisPath);
	    }

            // Supprimer le token
            $token->delete();

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
    public function check(Request $request, $noToken){
        $token = Token::where('token', $noToken)->first();

        if (!$token) {
            return response()->json(['exists' => false], 200);
        }

        if(!$request->hasHeader('secret-token')){
            return response()->json(['error'=>'No secret token provided.'], 403);  
        }
        
        $secretToken = config("secrets.$token->organisation_id"); 
        $adminToken = config('secrets.admin'); 

        $providedToken = $request->header('secret-token');

        if(!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)){
            return response()->json(['error'=>'Not authorized.'], 403);
        }

        $devisDir = $token->devis_id . '_' . $noToken;

        $pdfPath = $token->organisation_id . '/devis/' . $devisDir . '/' . $devisDir . '.pdf';
        $pdfPathCertif = $token->organisation_id . '/devis/' . $devisDir . '/' . $devisDir . '_certifie.pdf';

        try {
            
            $exists = Storage::disk('public')->exists($pdfPath) || Storage::disk('public')->exists($pdfPathCertif);

            return response()->json(['exists' => $exists], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la verification : ' . $e->getMessage());

            return response()->json(['error' => 'Erreur lors de la verification'], 500);
        }
    }

}
