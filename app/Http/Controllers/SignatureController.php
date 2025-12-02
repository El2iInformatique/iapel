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
                
                \Log::info("DEBUG: Positions finales pour l'image", [
                    'xImage' => $xImage,
                    'yImage' => $yImage,
                    'ratioConversion' => $ratioConversion
                ]);
                
                \Log::info("DEBUG: Tentative d'ajout de l'image au PDF");
                
                try {
                    $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    \Log::info("DEBUG: Image signature ajoutée au PDF avec succès");
                } catch (\Exception $e) {
                    \Log::error("DEBUG: Erreur lors de l'ajout de l'image", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    throw $e;
                }

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

    // Gérer la signature par initiales
    public function signWithInitials(Request $request, $token)
    {
        \Log::info("DEBUG: Début du processus de signature par initiales pour le token : " . $token);
        
        $request->validate([
            'initials' => 'required|string|max:3' // Initiales (maximum 3 caractères)
        ]);
        \Log::info("DEBUG: Validation des initiales réussie");

        $tokenEntry = Token::where('token', $token)
            ->first();

        \Log::info("DEBUG: Recherche du token dans la base de données", ['token_found' => $tokenEntry ? 'true' : 'false']);
        
        if (!$tokenEntry) {
            \Log::error("DEBUG: Token non trouvé ou invalide", ['token' => $token]);
            return response()->json(['message' => 'Token invalide'], Response::HTTP_FORBIDDEN);
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

        // Générer une signature basée sur les initiales
        $initials = strtoupper($request->input('initials'));
        
        // Créer une image de signature basée sur les initiales
        $uid = $tokenEntry->devis_id;
        $document = 'devis';
        $nomDoc = $uid . '_' . $token;
        $client = $tokenEntry->organisation_id;
        
        // Créer un répertoire s'il n'existe pas
        $signatureDir = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc);
        if (!file_exists($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }
        
        $signaturePath = $signatureDir . '/' . $nomDoc . '_signature.png';
        
        // Générer l'image de signature avec les initiales
        $this->generateInitialsSignature($initials, $signaturePath);
        \Log::info("DEBUG: Signature par initiales générée", ['signaturePath' => $signaturePath]);

        // Continuer avec le même processus que la signature manuelle
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '.pdf');
        $outputPath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf');

        \Log::info("DEBUG: Chemins des fichiers calculés", [
            'pdfPath' => $pdfPath,
            'outputPath' => $outputPath,
            'pdf_exists' => file_exists($pdfPath)
        ]);

        // Charger le PDF source
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        \Log::info("DEBUG: Instance FPDI créée");

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
                \Log::info("DEBUG: Application de la signature par initiales sur la page", ['page' => $i]);
                
                $ratioConversion = 6.98;

                $xDate = $tokenEntry->x_date;
                $yDate = $tokenEntry->y_date;
                $xSignature = $tokenEntry->x_signature;
                $ySignature = $tokenEntry->y_signature;

                // Intégration de la date
                $date_signature = date('d/m/Y');
                $pdf->SetXY($xDate/$ratioConversion, $yDate/$ratioConversion);
                $pdf->Write(10, $date_signature);
                \Log::info("DEBUG: Date ajoutée au PDF", ['date' => $date_signature]);

                // Intégration de la signature par initiales
                list($width, $height) = getimagesize($signaturePath);
                $maxWidth = 31;
                $maxHeight = 13;
                
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                
                $xImage = $xSignature / $ratioConversion; 
                $yImage = $ySignature / $ratioConversion;
                
                try {
                    $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    \Log::info("DEBUG: Image signature par initiales ajoutée au PDF avec succès");
                } catch (\Exception $e) {
                    \Log::error("DEBUG: Erreur lors de l'ajout de l'image", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    throw $e;
                }
            }
        }

        // Configuration PDF
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->Output($outputPath, 'F');
        \Log::info("DEBUG: PDF signé avec initiales généré", ['outputPath' => $outputPath]);

        // Signature électronique
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf');
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_certifie.pdf');
        $scriptPath = storage_path('app/signature/sign.py');
        
        $pythonPath = trim(shell_exec("which python3"));
        $process = new Process([$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            \Log::error("DEBUG: Échec du processus Python pour signature initiales", [
                'exit_code' => $process->getExitCode(),
                'error_output' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process);
        }

        \Log::info("DEBUG: Signature électronique par initiales réussie");

        // Suppression des fichiers temporaires
        unlink($outputPath);
        unlink($signaturePath);
        \Log::info("DEBUG: Processus de signature par initiales terminé avec succès");
        
        return response()->json(['success' => true, 'message' => 'Signature par initiales réussie']);
    }

    /**
     * Génère une image de signature basée sur les initiales
     */
    private function generateInitialsSignature($initials, $outputPath)
    {
        // Dimensions de l'image plus grandes pour une meilleure qualité
        $width = 600;
        $height = 300;
        
        // Créer une image vide avec support alpha pour la transparence
        $image = imagecreatetruecolor($width, $height);
        
        // Activer le canal alpha pour la transparence
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Définir les couleurs
        $backgroundColor = imagecolorallocatealpha($image, 255, 255, 255, 127); // Blanc transparent
        $textColor = imagecolorallocate($image, 0, 0, 0); // Noir au lieu de violet
        
        // Remplir avec le fond transparent
        imagefill($image, 0, 0, $backgroundColor);
        
        // Essayer d'utiliser une police TrueType si disponible
        $fontPath = null;
        $possibleFonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', // Linux
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf', // Linux alternative
            '/usr/share/fonts/TTF/arial.ttf', // Linux alternative
            '/System/Library/Fonts/Arial.ttf', // macOS
            '/Windows/Fonts/arial.ttf', // Windows
            '/Windows/Fonts/calibri.ttf', // Windows alternative
        ];
        
        foreach ($possibleFonts as $fontFile) {
            if (file_exists($fontFile)) {
                $fontPath = $fontFile;
                break;
            }
        }
        
        $angle = -5; // Légère rotation
        $fontSize = 72; // Police plus grande pour une meilleure qualité
        
        if ($fontPath && function_exists('imagettftext')) {
            // Utiliser une police TrueType
            try {
                // Calculer la position pour centrer le texte de manière sécurisée
                $textBox = imagettfbbox($fontSize, $angle, $fontPath, $initials);
                if ($textBox !== false) {
                    $textWidth = $textBox[4] - $textBox[0];
                    $textHeight = $textBox[1] - $textBox[5];
                    $x = ($width - $textWidth) / 2 - $textBox[0];
                    $y = ($height - $textHeight) / 2 - $textBox[5];
                } else {
                    // Fallback position
                    $x = $width / 2 - (strlen($initials) * 30);
                    $y = $height / 2 + 30;
                }
                
                // Dessiner le texte avec la police TrueType
                imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontPath, $initials);
                
                \Log::info("DEBUG: Police TrueType utilisée avec succès", [
                    'fontPath' => $fontPath,
                    'fontSize' => $fontSize,
                    'x' => $x,
                    'y' => $y,
                    'textColor' => 'noir (0,0,0)'
                ]);
                
            } catch (\Exception $e) {
                \Log::warning("DEBUG: Erreur avec police TrueType, fallback vers police système", [
                    'error' => $e->getMessage(),
                    'fontPath' => $fontPath
                ]);
                $fontPath = null; // Forcer le fallback
            }
        }
        
        if (!$fontPath) {
            // Fallback avec une police système (plus robuste)
            $systemFontSize = 5; // Taille maximale de police système (1-5)
            
            // Répéter le texte plusieurs fois pour simuler une police plus épaisse
            for ($offset = 0; $offset <= 2; $offset++) {
                // Calculer la position pour centrer le texte avec police système
                $textWidth = strlen($initials) * imagefontwidth($systemFontSize);
                $textHeight = imagefontheight($systemFontSize);
                $x = ($width - $textWidth) / 2 + $offset;
                $y = ($height - $textHeight) / 2 + $offset;
                
                // Pour simuler la rotation avec une police système, décaler légèrement
                $x += 20;
                
                // Dessiner le texte avec la police système plusieurs fois pour plus d'épaisseur
                imagestring($image, $systemFontSize, $x, $y, $initials, $textColor);
            }
            
            \Log::info("DEBUG: Police système utilisée avec épaississement", [
                'systemFontSize' => $systemFontSize,
                'textColor' => 'noir (0,0,0)',
                'technique' => 'répétition pour épaisseur'
            ]);
        }
        
        // Sauvegarder l'image en PNG avec transparence et compression optimale
        imagepng($image, $outputPath, 0); // 0 = pas de compression pour préserver la qualité
        imagedestroy($image);
        
        // Vérifier que le fichier a bien été créé et a une taille raisonnable
        if (file_exists($outputPath)) {
            $fileSize = filesize($outputPath);
            \Log::info("DEBUG: Image de signature par initiales créée avec succès", [
                'initials' => $initials,
                'outputPath' => $outputPath,
                'fontPath' => $fontPath ?? 'police système épaissie',
                'fileSize' => $fileSize . ' bytes',
                'dimensions' => $width . 'x' . $height,
                'color' => 'noir'
            ]);
        } else {
            \Log::error("DEBUG: Échec de la création de l'image de signature");
        }
    }
}
