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
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $devisName . '/' . $devisName . '.pdf');
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $devisName . '/' . $devisName . '_certifie.pdf');

        // Calcul du temps restant (devis valide 30 jours)
        $tempsRestants = 0;
        $signable = false;
        
        if (file_exists($pdfPath)) {
            $dateCreation = \Carbon\Carbon::createFromTimestamp(filemtime($pdfPath));
            $joursEcoules = (int) substr((string)$dateCreation->diffInDays(\Carbon\Carbon::now()), 0, 2);
            $tempsRestants = max(0, 30 - $joursEcoules);
            $signable = $tempsRestants > 0;
        }

        if (file_exists($pdfCertifiePath)) {
            return view('signature_download', [
                'token' => $token,
                'devis_id' => $tokenEntry->devis_id,
                'organisation_id' => $tokenEntry->organisation_id,
                'titre' => $tokenEntry->titre,
                'montant_HT' => $tokenEntry->montant_HT,
                'montant_TVA' => $tokenEntry->montant_TVA,
                'montant_TTC' => $tokenEntry->montant_TTC,
                'temps_restants' => $tempsRestants,
                'signable' => $signable
            ]);
        } else {
            return view('signature', [
                'token' => $token,
                'devis_id' => $tokenEntry->devis_id,
                'organisation_id' => $tokenEntry->organisation_id,
                'titre' => $tokenEntry->titre,
                'montant_HT' => $tokenEntry->montant_HT,
                'montant_TVA' => $tokenEntry->montant_TVA,
                'montant_TTC' => $tokenEntry->montant_TTC,
                'temps_restants' => $tempsRestants,
                'signable' => $signable
            ]);
        }
    }

    /**
     * @brief Signe un devis avec la signature manuscrite du client.
     * 
     * Processus en 3 étapes :
     * 1. Intègre la signature base64 du client dans le PDF
     * 2. Génère un fichier intermédiaire signé (non certifié)
     * 3. Fait signer électroniquement le PDF par un script Python (certificat)
     */
    public function sign(Request $request, $token)
    {
        \Log::info("DEBUG: Début du processus de signature pour le token : " . $token);
        
        // === VALIDATION ===
        // La signature arrive en base64 depuis l'appli frontend (SignaturePad.js)
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

        // === ÉTAPE 1 : INTÉGRATION DE LA SIGNATURE DANS LE PDF ===
        // Charge le PDF du devis et ajoute l'image de signature du client
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        \Log::info("DEBUG: Instance FPDI créée");

        // Récupère le nombre total de pages pour traiter chacune
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
                
                // === COORDONNÉES DE PLACEMENT ===
                // Les coordonnées en base de données sont en pixels (du frontend)
                // ratioConversion = conversion pixels → millimètres pour FPDI
                $hauteurSignature = $tokenEntry->position_signature;
                $ratioConversion = 6.98; // ratio calibré : 1 pixel = 6.98 autres unités
                
                // Récupère les coordonnées X,Y stockées pour date et signature
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

        // === ÉTAPE 2 : SIGNATURE ÉLECTRONIQUE (CERTIFICAT) ===
        // Lance un script Python qui applique une signature électronique certificative
        // Cela ajoute une preuve légale que le PDF n'a pas été modifié après signature
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf'); // PDF intermédiaire avec signature manuscrite
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_certifie.pdf'); // PDF final certifié
        $scriptPath = storage_path('app/signature/sign.py'); // Script Python pour signature électronique
        
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

        // === VÉRIFICATION DE LA SIGNATURE ÉLECTRONIQUE ===
        // Si le script Python échoue, on log l'erreur et on rejette la signature
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

    /**
     * @brief Signe un devis avec le nom et prénom du client (signature typographique).
     * 
     * Alternative à la signature manuscrite :
     * - Génère une image de signature élégante avec le nom complet du client
     * - Puis suit le même processus que sign() : intégration + certification électronique
     */
    public function signWithFullName(Request $request, $token)
    {
        \Log::info("DEBUG: Début du processus de signature par nom et prénom pour le token : " . $token);
        
        // === VALIDATION DES ENTRÉES ===
        // Valide que les champs nom et prénom ne sont pas vides
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50'
        ]);
        \Log::info("DEBUG: Validation du nom et prénom réussie");

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

        // Générer une signature basée sur le nom et prénom
        $firstname = ucfirst(strtolower(trim($request->input('firstname'))));
        $lastname = ucfirst(strtolower(trim($request->input('lastname'))));
        
        // Créer un répertoire s'il n'existe pas
        $uid = $tokenEntry->devis_id;
        $document = 'devis';
        $nomDoc = $uid . '_' . $token;
        $client = $tokenEntry->organisation_id;
        
        $signatureDir = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc);
        if (!file_exists($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }
        
        $signaturePath = $signatureDir . '/' . $nomDoc . '_signature.png';
        
        // Générer l'image de signature avec le nom et prénom complets
        $this->generateFullNameSignature($firstname, $lastname, $signaturePath);
        \Log::info("DEBUG: Signature par nom et prénom générée", ['signaturePath' => $signaturePath]);

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
                \Log::info("DEBUG: Application de la signature par nom et prénom sur la page", ['page' => $i]);
                
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

                // Intégration de la signature par nom et prénom
                list($width, $height) = getimagesize($signaturePath);
                
                // TAILLE AUGMENTÉE pour meilleure lisibilité dans le PDF
                $maxWidth = 50;  // Augmentation de 31 à 50mm
                $maxHeight = 20; // Augmentation de 13 à 20mm
                
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                
                $xImage = $xSignature / $ratioConversion; 
                $yImage = $ySignature / $ratioConversion;
                
                \Log::info("DEBUG: Dimensions signature dans PDF", [
                    'maxWidth' => $maxWidth,
                    'maxHeight' => $maxHeight,
                    'newWidth' => $newWidth,
                    'newHeight' => $newHeight
                ]);
                
                try {
                    $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    \Log::info("DEBUG: Image signature par nom et prénom ajoutée au PDF avec succès");
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
        \Log::info("DEBUG: PDF signé avec nom et prénom généré", ['outputPath' => $outputPath]);

        // Signature électronique
        $pdfSignePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_signe.pdf');
        $pdfCertifiePath = storage_path('app/public/' . $client . '/' . $document . '/' . $nomDoc . '/' . $nomDoc . '_certifie.pdf');
        $scriptPath = storage_path('app/signature/sign.py');
        
        $pythonPath = trim(shell_exec("which python3"));
        $process = new Process([$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            \Log::error("DEBUG: Échec du processus Python pour signature nom et prénom", [
                'exit_code' => $process->getExitCode(),
                'error_output' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process);
        }

        \Log::info("DEBUG: Signature électronique par nom et prénom réussie");

        // Suppression des fichiers temporaires
        unlink($outputPath);
        unlink($signaturePath);
        \Log::info("DEBUG: Processus de signature par nom et prénom terminé avec succès");
        
        return response()->json(['success' => true, 'message' => 'Signature par nom et prénom réussie']);
    }

    /**
     * Génère une signature élégante et professionnelle à partir du nom et prénom complets
     */
    private function generateFullNameSignature($firstname, $lastname, $outputPath)
    {
        // Dimensions de l'image optimisées pour une signature professionnelle
        $width = 1200;  // Augmenté pour meilleure qualité
        $height = 400;   // Augmenté pour meilleure qualité
        
        // Créer une image vide avec support alpha pour la transparence
        $image = imagecreatetruecolor($width, $height);
        
        // Activer le canal alpha pour la transparence
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Définir les couleurs
        $backgroundColor = imagecolorallocatealpha($image, 255, 255, 255, 127); // Transparent
        $textColor = imagecolorallocate($image, 0, 0, 0); // Noir pur pour meilleure lisibilité
        $lineColor = imagecolorallocate($image, 0, 0, 0); // Ligne noire
        $shadowColor = imagecolorallocatealpha($image, 50, 50, 50, 40); // Ombre très subtile
        
        // Remplir avec le fond transparent
        imagefill($image, 0, 0, $backgroundColor);
        
        // Liste de polices PRIORISÉE POUR UBUNTU/LINUX SERVER
        $possibleFonts = [
            // POLICE PERSONNALISÉE MANUSCRITE - PRIORITÉ ABSOLUE
            '/var/www/laravel/fonts/BrittanySignature.ttf',
            base_path('fonts/BrittanySignature.ttf'), // Chemin Laravel alternatif
            // Ubuntu/Debian - Polices par défaut les plus courantes
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Italic.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Oblique.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            // Chemins alternatifs pour certaines distributions
            '/usr/share/fonts/TTF/LiberationSerif-Italic.ttf',
            '/usr/share/fonts/TTF/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/TTF/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/TTF/DejaVuSerif.ttf',
            // Polices Ubuntu spécifiques
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-Italic.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-Regular.ttf',
            // Windows (au cas où en développement local)
            'C:/Windows/Fonts/times.ttf',
            'C:/Windows/Fonts/timesi.ttf',
            'C:/Windows/Fonts/georgia.ttf',
            'C:/Windows/Fonts/georgiai.ttf',
            // macOS (au cas où)
            '/System/Library/Fonts/Times New Roman Italic.ttf',
            '/System/Library/Fonts/Georgia Italic.ttf',
        ];
        
        $fontPath = null;
        $fontName = 'Non détectée';
        
        foreach ($possibleFonts as $fontFile) {
            if (file_exists($fontFile)) {
                $fontPath = $fontFile;
                $fontName = basename($fontFile);
                \Log::info("DEBUG: Police trouvée", ['font' => $fontPath]);
                break;
            }
        }
        
        $fullName = $firstname . ' ' . $lastname;
        
        if ($fontPath && function_exists('imagettftext')) {
            // POLICE PLUS GRANDE ET PLUS LISIBLE
            $fontSize = 90; // 90pt pour excellente lisibilité
            $angle = -2; // Inclinaison très subtile pour effet élégant
            
            try {
                // Calculer les dimensions du texte pour le centrer
                $textBox = imagettfbbox($fontSize, $angle, $fontPath, $fullName);
                if ($textBox !== false) {
                    $textWidth = $textBox[4] - $textBox[0];
                    $textHeight = $textBox[1] - $textBox[5];
                    $x = ($width - $textWidth) / 2 - $textBox[0];
                    $y = ($height - $textHeight) / 2 - $textBox[5];
                } else {
                    $x = $width / 2 - (strlen($fullName) * 30);
                    $y = $height / 2 + 30;
                }
                
                // Ajouter une ombre portée très subtile pour la profondeur
                imagettftext($image, $fontSize, $angle, $x + 2, $y + 2, $shadowColor, $fontPath, $fullName);
                
                // Texte principal en noir pur - TRAIT ÉPAISSI
                imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontPath, $fullName);
                // Deuxième passage pour épaissir légèrement
                imagettftext($image, $fontSize, $angle, $x + 0.5, $y, $textColor, $fontPath, $fullName);
                
                // Ajouter une ligne décorative sous la signature
                $lineY = $y + 30;
                $lineStartX = $x;
                $lineEndX = $x + $textWidth;
                
                // Ligne principale noire - ÉPAISSIE
                $this->drawSmoothLine($image, $lineStartX, $lineY, $lineEndX, $lineY, $lineColor, 3);
                
                // Flourish décoratif au début
                $this->drawFlourishStart($image, $lineStartX - 15, $lineY, $lineColor);
                
                \Log::info("DEBUG: Signature élégante créée avec police TrueType", [
                    'font' => $fontName,
                    'fontPath' => $fontPath,
                    'fullName' => $fullName,
                    'fontSize' => $fontSize,
                    'dimensions' => $width . 'x' . $height,
                    'color' => 'noir pur',
                    'style' => 'professionnel avec flourish',
                    'os' => 'Ubuntu/Linux'
                ]);
                
            } catch (\Exception $e) {
                \Log::warning("DEBUG: Erreur avec police TrueType, fallback vers style personnalisé", [
                    'error' => $e->getMessage()
                ]);
                $fontPath = null;
            }
        }
        
        if (!$fontPath) {
            // Fallback : créer une signature élégante avec police système
            \Log::warning("DEBUG: Aucune police TrueType trouvée, utilisation du fallback", [
                'searched_paths' => array_slice($possibleFonts, 0, 5)
            ]);
            $this->createElegantHandwrittenSignature($image, $fullName, $textColor, $lineColor, $width, $height);
        }
        
        // Sauvegarder l'image en PNG haute qualité
        imagepng($image, $outputPath, 0);
        imagedestroy($image);
        
        // Vérification finale
        if (file_exists($outputPath)) {
            $fileSize = filesize($outputPath);
            \Log::info("DEBUG: Signature professionnelle créée", [
                'fullName' => $fullName,
                'outputPath' => $outputPath,
                'fileSize' => $fileSize . ' bytes',
                'dimensions' => $width . 'x' . $height,
                'font' => $fontName,
                'color' => 'noir pur',
                'style' => $fontPath ? 'police professionnelle' : 'manuscrit élégant personnalisé'
            ]);
        }
    }
    
    /**
     * Crée une signature manuscrite élégante avec un style professionnel
     */
    private function createElegantHandwrittenSignature($image, $fullName, $textColor, $lineColor, $width, $height)
    {
        // Pour le fallback, on utilise une approche avec police système plus grande
        $centerX = $width / 2;
        $centerY = $height / 2;
        
        // Dessiner le nom en style élégant - TAILLE AUGMENTÉE
        $fontSize = 5; // Police système grande taille
        $textWidth = imagefontwidth($fontSize) * strlen($fullName);
        $textHeight = imagefontheight($fontSize);
        
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        // Ombre très subtile
        $shadowColor = imagecolorallocatealpha($image, 50, 50, 50, 40);
        imagestring($image, $fontSize, $x + 2, $y + 2, $fullName, $shadowColor);
        
        // Texte principal en noir pur
        imagestring($image, $fontSize, $x, $y, $fullName, $textColor);
        
        // Ligne décorative noire
        $lineY = $y + $textHeight + 10;
        imagesetthickness($image, 2);
        imageline($image, $x, $lineY, $x + $textWidth, $lineY, $lineColor);
        imagesetthickness($image, 1);
        
        \Log::info("DEBUG: Signature élégante créée avec police système", [
            'fullName' => $fullName,
            'color' => 'noir pur',
            'technique' => 'police système avec décoration'
        ]);
    }
    
    /**
     * Dessine une ligne lisse avec antialiasing
     */
    private function drawSmoothLine($image, $x1, $y1, $x2, $y2, $color, $thickness)
    {
        imagesetthickness($image, $thickness);
        imageline($image, $x1, $y1, $x2, $y2, $color);
        imagesetthickness($image, 1);
    }
    
    /**
     * Dessine un flourish décoratif au début de la signature
     */
    private function drawFlourishStart($image, $x, $y, $color)
    {
        // Petite courbe décorative
        $points = [
            $x - 15, $y - 5,
            $x - 10, $y - 10,
            $x - 5, $y - 8,
            $x, $y
        ];
        
        imagesetthickness($image, 2);
        // Dessiner la courbe avec plusieurs segments
        for ($i = 0; $i < count($points) - 2; $i += 2) {
            imageline($image, $points[$i], $points[$i+1], $points[$i+2], $points[$i+3], $color);
        }
        imagesetthickness($image, 1);
    }
}
