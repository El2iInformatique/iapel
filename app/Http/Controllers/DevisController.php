<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class DevisController extends Controller
{
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
