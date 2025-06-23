<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


class DevisController extends Controller
{
    public function delete(Request $request, $token): JsonResponse
    {
        $dataToken = Token::where('token', $token)->first();
        $filePath = storage_path( $dataToken->paths);

        if (!file_exists($filePath)) {
            abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);

        $devisDir = $data['devis_id'];

	    $devisPath = $data['organisation_id'] . '/devis/' . $devisDir . '/';
        $pdfPath = $data['organisation_id'] . '/devis/' . $devisDir . '/' . $devisDir . '.pdf';
        $pdfPathCertif = $data['organisation_id'] . '/devis/' . $devisDir . '/' . $devisDir . '_certifie.pdf';
        
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
            $dataToken->delete();

            return response()->json(['success' => 'Devis supprimé avec succès'], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression : ' . $e->getMessage());

            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    public function check(Request $request, $token){

        $dataToken = Token::where('token', $token)->first();

        $filePath = storage_path( $dataToken->paths);

        if (!file_exists($filePath)) {
            return abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);


        $devisDir = $data["devis_id"];

        $pdfPath = $data['organisation_id'] . '/devis/' . $devisDir . '/' . $devisDir . '.pdf';
        $pdfPathCertif = $data['organisation_id'] . '/devis/' . $devisDir . '/' . $devisDir . '_certifie.pdf';

        try {
            
            $exists = Storage::disk('public')->exists($pdfPath) || Storage::disk('public')->exists($pdfPathCertif);

            return response()->json(['exists' => $exists], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la verification : ' . $e->getMessage());

            return response()->json(['error' => 'Erreur lors de la verification'], 500);
        }
    }


    public function download_devis($token){

        $tokenEntry = Token::where("token", $token)->first();
        Log::info("Demande de téléchargement d'un devis : " . $token);

        $filePath = storage_path( $tokenEntry['paths']);

        if (!file_exists($filePath)) {
            return abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        $filePath = storage_path('app/public/'.$client.'/devis/'.$uid. '/' . $uid . '.pdf');
        if (!file_exists($filePath)) {
            abort(404);
        }

        return \Response::file($filePath, headers: [
            'Content-Type' => 'application/pdf',
        ]);
    }
    

    public function download_devis_certifie ($token) {
        
        $tokenEntry = Token::where("token", $token)->first();
        Log::info("Demande de téléchargement d'un devis certifié : " . $token);

        $filePath = storage_path( $tokenEntry['paths']);

        if (!file_exists($filePath)) {
            return abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        $filePath = storage_path('app/public/'.$client.'/devis/'.$uid. '/' . $uid . '_certifie.pdf');
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, "{$uid}.pdf");
    }


}
