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
            return response()->view('errors.403', [], Response::HTTP_FORBIDDEN);
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
        $request->validate([
            'signature' => 'required|string' // Signature encodée en base64
        ]);

        $tokenEntry = Token::where('token', $token)
            //->where('used', false)
            //->where('expires_at', '>', now())
            ->first();

        if (!$tokenEntry) {
            //return response()->json(['message' => 'Token invalide ou expiré'], Response::HTTP_FORBIDDEN);
        }

        // Marquer le token comme utilisé
        $tokenEntry->used = true;
        $tokenEntry->save();

        // On modifie le pdf pour inclure la signature du client
        $uid = $tokenEntry->devis_id;
        $document = 'devis';
        $nomDoc = $uid . '_' . $token;
        $client = $tokenEntry->organisation_id;
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '.pdf'); // PDF du devis initial
        $outputPath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf'); // PDF du devis avec signature client

        // Charger le PDF source
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        // ombre total de pages dans le PDF source
        $pageCount = $pdf->setSourceFile($pdfPath);

        // Importer toutes les pages
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

            // Si avant-dernière page, appliquer signature et date
            if ($i == $pageCount - 1) {
                $hauteurSignature = $tokenEntry->position_signature;

                $ratioConversion = 6.98; // ce 6.98 a l'air de sortir du chapeau mais je jure que c'est un ratio correct que j'ai calculé a la main

                $xDate = $tokenEntry->x_date;
                $yDate = $tokenEntry->y_date;

                $xSignature = $tokenEntry->x_signature;
                $ySignature = $tokenEntry->y_signature;

                // Intégration de la date
                $date_signature = date('d/m/Y');
                $pdf->SetXY( $xDate/$ratioConversion, $yDate/$ratioConversion);
                $pdf->Write(10, $date_signature);

                // Intégration de la signature
                $signatureBase64 = $request->input('signature');
                $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
                $signaturePath = storage_path('app/public/' . $client . '/' . $document . '/'  . $nomDoc . '/' . $nomDoc . '_signature.png');
                file_put_contents($signaturePath, $signatureData);
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
                // Calcul des positions pour centrer dans le carré

                $xImage = $xSignature / $ratioConversion; 
                $yImage = $ySignature / $ratioConversion;
                $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);

                /** Mettre page de fin ici */

            }
        }

        // Désactiver les en-têtes et pieds de page
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);




        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath, 'F');


        // On va signer électroniquement le devis
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf'); // PDF du devis avec signature client
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_certifie.pdf'); // PDF du devis avec signature client
        $scriptPath = storage_path('app/signature/sign.py');
        $pythonPath = trim(shell_exec("which python3"));
        $process = new Process([$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath]);
        $process->run();

        // 📌 Vérifier si le script a échoué
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process); // Affiche l'erreur dans Laravel
        }

        // Suppression des fichiers temporaires
        unlink($outputPath);
        unlink($signaturePath);
    }
}
