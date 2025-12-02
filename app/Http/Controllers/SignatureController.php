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
     * Génère une image de signature basée sur les initiales avec un style manuscrit
     */
    private function generateInitialsSignature($initials, $outputPath)
    {
        // Dimensions de l'image
        $width = 600;
        $height = 300;
        
        // Créer une image vide avec support alpha pour la transparence
        $image = imagecreatetruecolor($width, $height);
        
        // Activer le canal alpha pour la transparence
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Définir les couleurs
        $backgroundColor = imagecolorallocatealpha($image, 255, 255, 255, 127); // Blanc transparent
        $textColor = imagecolorallocate($image, 0, 0, 0); // Noir
        $shadowColor = imagecolorallocate($image, 128, 128, 128); // Gris pour l'ombre
        
        // Remplir avec le fond transparent
        imagefill($image, 0, 0, $backgroundColor);
        
        // Essayer d'utiliser une police cursive/manuscrite si disponible
        $fontPath = null;
        $possibleFonts = [
            // Polices cursives/manuscrites sur différents systèmes
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf', // Linux
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf', // Linux
            '/System/Library/Fonts/Brush Script.ttf', // macOS
            '/System/Library/Fonts/Bradley Hand Bold.ttf', // macOS
            '/Windows/Fonts/brushsci.ttf', // Windows Brush Script
            '/Windows/Fonts/BRITANIC.TTF', // Windows Britannic Bold
            '/usr/share/fonts/TTF/LiberationSerif-Italic.ttf', // Linux alternative
        ];
        
        foreach ($possibleFonts as $fontFile) {
            if (file_exists($fontFile)) {
                $fontPath = $fontFile;
                break;
            }
        }
        
        $angle = -8; // Rotation plus marquée pour effet manuscrit
        $fontSize = 80; // Police plus grande
        
        if ($fontPath && function_exists('imagettftext')) {
            // Utiliser une police TrueType avec style manuscrit
            try {
                // Calculer la position pour centrer le texte
                $textBox = imagettfbbox($fontSize, $angle, $fontPath, $initials);
                if ($textBox !== false) {
                    $textWidth = $textBox[4] - $textBox[0];
                    $textHeight = $textBox[1] - $textBox[5];
                    $x = ($width - $textWidth) / 2 - $textBox[0];
                    $y = ($height - $textHeight) / 2 - $textBox[5];
                } else {
                    $x = $width / 2 - (strlen($initials) * 35);
                    $y = $height / 2 + 35;
                }
                
                // Ajouter une ombre légère pour plus de profondeur
                imagettftext($image, $fontSize, $angle, $x + 3, $y + 3, $shadowColor, $fontPath, $initials);
                // Texte principal
                imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontPath, $initials);
                
                \Log::info("DEBUG: Police TrueType manuscrite utilisée", [
                    'fontPath' => $fontPath,
                    'fontSize' => $fontSize,
                    'angle' => $angle,
                    'style' => 'avec ombre'
                ]);
                
            } catch (\Exception $e) {
                \Log::warning("DEBUG: Erreur avec police TrueType, fallback vers style manuscrit personnalisé", [
                    'error' => $e->getMessage()
                ]);
                $fontPath = null;
            }
        }
        
        if (!$fontPath) {
            // Créer un style manuscrit personnalisé avec la police système
            $this->createHandwrittenStyle($image, $initials, $textColor, $width, $height);
        }
        
        // Sauvegarder l'image
        imagepng($image, $outputPath, 0);
        imagedestroy($image);
        
        // Vérification finale
        if (file_exists($outputPath)) {
            $fileSize = filesize($outputPath);
            \Log::info("DEBUG: Signature manuscrite créée", [
                'initials' => $initials,
                'outputPath' => $outputPath,
                'fileSize' => $fileSize . ' bytes',
                'style' => $fontPath ? 'police cursive' : 'manuscrit personnalisé'
            ]);
        }
    }
    
    /**
     * Crée un style manuscrit personnalisé en dessinant les lettres à la main
     */
    private function createHandwrittenStyle($image, $initials, $textColor, $width, $height)
    {
        $letters = str_split(strtoupper($initials));
        $letterWidth = $width / (count($letters) + 1);
        $baseY = $height / 2;
        
        foreach ($letters as $index => $letter) {
            $x = $letterWidth * ($index + 1);
            $y = $baseY + rand(-20, 20); // Variation verticale pour effet manuscrit
            
            // Dessiner chaque lettre avec un style manuscrit
            $this->drawHandwrittenLetter($image, $letter, $x, $y, $textColor);
        }
        
        \Log::info("DEBUG: Style manuscrit personnalisé appliqué", [
            'letters' => $letters,
            'letterCount' => count($letters),
            'technique' => 'dessin vectoriel personnalisé'
        ]);
    }
    
    /**
     * Dessine une lettre avec un style manuscrit personnalisé
     */
    private function drawHandwrittenLetter($image, $letter, $centerX, $centerY, $color)
    {
        $thickness = 4; // Épaisseur du trait
        
        // Dessiner chaque lettre avec des courbes manuscrites
        switch ($letter) {
            case 'A':
                // Trait gauche
                $this->drawThickLine($image, $centerX - 30, $centerY + 40, $centerX - 10, $centerY - 40, $color, $thickness);
                // Trait droit
                $this->drawThickLine($image, $centerX + 10, $centerY - 40, $centerX + 30, $centerY + 40, $color, $thickness);
                // Barre horizontale
                $this->drawThickLine($image, $centerX - 15, $centerY, $centerX + 15, $centerY, $color, $thickness);
                break;
                
            case 'B':
                // Trait vertical
                $this->drawThickLine($image, $centerX - 25, $centerY - 40, $centerX - 25, $centerY + 40, $color, $thickness);
                // Courbe supérieure
                $this->drawCurve($image, $centerX - 25, $centerY - 40, $centerX + 20, $centerY - 20, $centerX - 25, $centerY, $color, $thickness);
                // Courbe inférieure
                $this->drawCurve($image, $centerX - 25, $centerY, $centerX + 25, $centerY + 20, $centerX - 25, $centerY + 40, $color, $thickness);
                break;
                
            case 'C':
                // Arc de cercle
                $this->drawArc($image, $centerX, $centerY, 50, 70, 30, 330, $color, $thickness);
                break;
                
            default:
                // Pour les autres lettres, dessiner un style générique élégant
                // Trait principal avec courbe
                $this->drawCurve($image, $centerX - 20, $centerY + 30, $centerX, $centerY - 30, $centerX + 20, $centerY + 20, $color, $thickness);
                // Flourish décoratif
                $this->drawCurve($image, $centerX - 15, $centerY - 20, $centerX + 5, $centerY - 35, $centerX + 25, $centerY - 15, $color, 2);
                break;
        }
    }
    
    /**
     * Dessine une ligne épaisse
     */
    private function drawThickLine($image, $x1, $y1, $x2, $y2, $color, $thickness)
    {
        for ($i = 0; $i < $thickness; $i++) {
            imageline($image, $x1 + $i, $y1, $x2 + $i, $y2, $color);
            imageline($image, $x1, $y1 + $i, $x2, $y2 + $i, $color);
        }
    }
    
    /**
     * Dessine une courbe manuscrite
     */
    private function drawCurve($image, $x1, $y1, $x2, $y2, $x3, $y3, $color, $thickness)
    {
        // Dessiner une courbe de Bézier simplifiée
        for ($t = 0; $t <= 1; $t += 0.01) {
            $x = (1 - $t) * (1 - $t) * $x1 + 2 * (1 - $t) * $t * $x2 + $t * $t * $x3;
            $y = (1 - $t) * (1 - $t) * $y1 + 2 * (1 - $t) * $t * $y2 + $t * $t * $y3;
            
            // Dessiner un point épais
            for ($i = 0; $i < $thickness; $i++) {
                for ($j = 0; $j < $thickness; $j++) {
                    imagesetpixel($image, $x + $i, $y + $j, $color);
                }
            }
        }
    }
    
    /**
     * Dessine un arc avec épaisseur
     */
    private function drawArc($image, $centerX, $centerY, $width, $height, $start, $end, $color, $thickness)
    {
        for ($i = 0; $i < $thickness; $i++) {
            imagearc($image, $centerX + $i, $centerY, $width, $height, $start, $end, $color);
            imagearc($image, $centerX, $centerY + $i, $width, $height, $start, $end, $color);
        }
    }
}
