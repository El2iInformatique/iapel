<?php

namespace App\Http\Controllers;

use App\Models\Token;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{

    // Afficher la page de signature
    public function show($token)
    {
        $tokenEntry = Token::where('token', $token)
            //->where('used', false)
            //->where('expires_at', '>', now())
            ->first();

        if (!$tokenEntry) {
            abort(403);
        }

        // On vérifie si le devis a été signé en vérifiant la présence du PDF certifié
        $uid = $tokenEntry->devis_id;
        $document = 'devis';
        $client = $tokenEntry->organisation_id;
        $devisName = $uid . '_' . $token;
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $devisName . '/' . $devisName . '_certifie.pdf'); // PDF du devis certifié

        if (file_exists($pdfPath)) {
            return view('signature_download', [
                'token' => $token,
                'devis_id' => $tokenEntry->devis_id,
                'organisation_id' => $tokenEntry->organisation_id,
                'titre' => $tokenEntry->titre,
                'montant_HT' => $tokenEntry->montant_HT,
                'montant_TVA' => $tokenEntry->montant_TVA,
                'montant_TTC' => $tokenEntry->montant_TTC
            ]);
        } else {
            return view('signature', [
                'token' => $token,
                'devis_id' => $tokenEntry->devis_id,
                'organisation_id' => $tokenEntry->organisation_id,
                'titre' => $tokenEntry->titre,
                'montant_HT' => $tokenEntry->montant_HT,
                'montant_TVA' => $tokenEntry->montant_TVA,
                'montant_TTC' => $tokenEntry->montant_TTC
            ]);
        }
    }

    // Gérer la signature
    public function sign(Request $request, $token)
    {
        \Log::info("DEBUG: Début du processus de signature pour le token : " . $token);
        
        $request->validate([
            'signature' => 'required|string' // Signature encodée en base64
        ]);
        \Log::info("DEBUG: Validation de la signature réussie");

        $tokenEntry = Token::where('token', $token)
            //->where('used', false)
            //->where('expires_at', '>', now())
            ->first();

        \Log::info("DEBUG: Recherche du token dans la base de données", ['token_found' => $tokenEntry ? 'true' : 'false']);
        
        if (!$tokenEntry) {
            \Log::error("DEBUG: Token non trouvé ou invalide", ['token' => $token]);
            //return response()->json(['message' => 'Token invalide ou expiré'], Response::HTTP_FORBIDDEN);
        }

        \Log::info("DEBUG: Informations du token", [
            'devis_id' => $tokenEntry->devis_id,
            'organisation_id' => $tokenEntry->organisation_id,
            'used' => $tokenEntry->used
        ]);

        // Marquer le token comme utilisé
        $tokenEntry->used = true;
        $tokenEntry->save();
        \Log::info("DEBUG: Token marqué comme utilisé");

        // On modifie le pdf pour inclure la signature du client
        $uid = $tokenEntry->devis_id;
        $document = 'devis';
        $nomDoc = $uid . '_' . $token;
        $client = $tokenEntry->organisation_id;
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '.pdf'); // PDF du devis initial
        $outputPath = storage_path(path: 'app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf'); // PDF du devis avec signature client

        \Log::info("DEBUG: Chemins des fichiers calculés", [
            'pdfPath' => $pdfPath,
            'outputPath' => $outputPath,
            'pdf_exists' => file_exists($pdfPath)
        ]);

        // Charger le PDF source
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        \Log::info("DEBUG: Instance FPDI créée");

        // ombre total de pages dans le PDF source
        $pageCount = $pdf->setSourceFile($pdfPath);
        \Log::info("DEBUG: PDF source chargé", ['pageCount' => $pageCount]);

        // Importer toutes les pages
        for ($i = 1; $i <= $pageCount; $i++) {
            \Log::info("DEBUG: Traitement de la page", ['page' => $i, 'total' => $pageCount]);
            
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

            // Si avant-dernière page, appliquer signature et date
            if ($i == $pageCount - $tokenEntry->nb_pages) {
                \Log::info("DEBUG: Application de la signature sur la page", ['page' => $i]);
                
                $hauteurSignature = $tokenEntry->position_signature;
                $ratioConversion = 6.98; // ce 6.98 a l'air de sortir du chapeau mais je jure que c'est un ratio correct que j'ai calculé a la main

                $xDate = $tokenEntry->x_date;
                $yDate = $tokenEntry->y_date;
                $xSignature = $tokenEntry->x_signature;
                $ySignature = $tokenEntry->y_signature;

                \Log::info("DEBUG: Positions calculées", [
                    'xDate' => $xDate,
                    'yDate' => $yDate,
                    'xSignature' => $xSignature,
                    'ySignature' => $ySignature
                ]);

                // Intégration de la date
                $date_signature = date('d/m/Y');
                $pdf->SetXY( $xDate/$ratioConversion, $yDate/$ratioConversion);
                $pdf->Write(10, $date_signature);
                \Log::info("DEBUG: Date ajoutée au PDF", ['date' => $date_signature]);

                // Intégration de la signature
                $signatureBase64 = $request->input('signature');
                $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
                $signaturePath = storage_path('app/public/' . $client . '/' . $document . '/'  . $nomDoc . '/' . $nomDoc . '_signature.png');
                file_put_contents($signaturePath, $signatureData);
                \Log::info("DEBUG: Fichier signature créé", ['signaturePath' => $signaturePath]);
                
                list($width, $height) = getimagesize($signaturePath); // Récupère la taille originale
                $maxWidth = 31;
                $maxHeight = 13;
                // Calcul du redimensionnement en conservant le ratio
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                
                \Log::info("DEBUG: Dimensions de la signature", [
                    'original_width' => $width,
                    'original_height' => $height,
                    'new_width' => $newWidth,
                    'new_height' => $newHeight
                ]);

                // Calcul des positions pour centrer dans le carré
                $xImage = $xSignature / $ratioConversion; 
                $yImage = $ySignature / $ratioConversion;
                $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                \Log::info("DEBUG: Image signature ajoutée au PDF");

                /** Mettre page de fin ici */

            }
        }

        // Désactiver les en-têtes et pieds de page
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        \Log::info("DEBUG: Configuration PDF finalisée");

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath, 'F');
        \Log::info("DEBUG: PDF signé généré", ['outputPath' => $outputPath, 'file_exists' => file_exists($outputPath)]);

        // On va signer électroniquement le devis
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf'); // PDF du devis avec signature client
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_certifie.pdf'); // PDF du devis avec signature client
        $scriptPath = storage_path('app/signature/sign.py');
        
        \Log::info("DEBUG: Préparation de la signature électronique", [
            'pdfSignePath' => $pdfSignePath,
            'pdfCertifiePath' => $pdfCertifiePath,
            'scriptPath' => $scriptPath,
            'script_exists' => file_exists($scriptPath)
        ]);
        
        $pythonPath = trim(shell_exec("which python3"));
        \Log::info("DEBUG: Chemin Python trouvé", ['pythonPath' => $pythonPath]);
        
        $process = new Process([$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath]);
        $process->run();

        \Log::info("DEBUG: Processus Python exécuté", [
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput()
        ]);

        // 📌 Vérifier si le script a échoué
        if (!$process->isSuccessful()) {
            \Log::error("DEBUG: Échec du processus Python", [
                'exit_code' => $process->getExitCode(),
                'error_output' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process); // Affiche l'erreur dans Laravel
        }

        \Log::info("DEBUG: Signature électronique réussie", ['pdfCertifiePath' => $pdfCertifiePath]);

        // Suppression des fichiers temporaires
        unlink($outputPath);
        unlink($signaturePath);
        \Log::info("DEBUG: Fichiers temporaires supprimés - Processus de signature terminé avec succès");
    }
}
