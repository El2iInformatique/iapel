<?php

namespace App\Http\Controllers;

use App\Models\Token;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{

    // Afficher la page de signature
    public function show($token)
    {
        $dataToken = Token::where('token', $token)
            //->where('used', false)
            //->where('expires_at', '>', now())
            ->first();

        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier JSON existe
        if (!file_exists($filePath)) {
            abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        // On vérifie si le devis a été signé en vérifiant la présence du PDF certifié
        $devisName = $uid . '_' . $token;
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $devisName . '/' . $devisName . '_certifie.pdf'); // PDF du devis certifié

        if (file_exists($pdfPath)) {
            return view('signature_download', [
                'token' => $token,
                'devis_id' => $data["devis_id"],
                'organisation_id' => $data["organisation_id"],
                'titre' => $data["titre"],
                'montant_HT' => $data["montant_HT"],
                'montant_TVA' => $data["montant_TVA"],
                'montant_TTC' => $data["montant_TTC"]
            ]);
        } else {
            return view('signature', [
                'token' => $token,
                'devis_id' => $data["devis_id"],
                'organisation_id' => $data["organisation_id"],
                'titre' => $data["titre"],
                'montant_HT' => $data["montant_HT"],
                'montant_TVA' => $data["montant_TVA"],
                'montant_TTC' => $data["montant_TTC"]
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


        // Marquer le token comme utilisé
        $tokenEntry->used = true;
        $tokenEntry->save();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $tokenEntry['paths']);

        if (!file_exists($filePath)) {
            return abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf'); // PDF du devis initial
        $outputPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '_signe.pdf'); // PDF du devis avec signature client

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
            if ($i == $pageCount - $tokenEntry->nb_pages) {
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
                $signaturePath = storage_path('app/public/' . $client . '/' . $document . '/'  . $uid . '/' . $uid . '_signature.png');
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
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '_signe.pdf'); // PDF du devis avec signature client
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '_certifie.pdf'); // PDF du devis avec signature client
        $scriptPath = storage_path('app/signature/sign.py');
        $pythonPath = "py"; //trim(shell_exec("which python3"));
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


    public function devis(Request $request, $token){

        $tokenEntry = Token::where("token", $token)->first();
        Log::info("Token : " . $tokenEntry);

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
}
