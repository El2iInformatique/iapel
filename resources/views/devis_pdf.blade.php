<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Devis</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-y: auto; /* Permet le scroll sur toute la fenêtre */
        }
        iframe {
            width: 100%;
            border: 1px solid #ccc; /* Bordure légère */
            border-radius: 5px;
        }
        .pdf-container {
            max-width: 900px; /* Largeur maximale du cadre */
            width: 90%; /* Largeur responsive */
            margin: 20px auto; /* Centrage vertical et horizontal */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            height: 1230px;
        }
        .download-container {
            max-width: 900px; /* Largeur maximale du cadre */
            width: 90%; /* Largeur responsive */
            margin: 20px auto; /* Centrage vertical et horizontal */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            height: 300px;
        }
        </style>
    </head>
    <body>
        <?php
            // Debug de départ
            error_log("=== DEBUG VUE DEVIS_PDF ===");
            error_log("Variables reçues:");
            error_log("- client: " . ($client ?? 'NON DÉFINI'));
            error_log("- token: " . ($token ?? 'NON DÉFINI'));
            error_log("- nomDevis: " . ($nomDevis ?? 'NON DÉFINI'));
            error_log("- isCertified: " . ($isCertified ? 'true' : 'false'));
            
            $isAndroid = stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
            error_log("- isAndroid: " . ($isAndroid ? 'true' : 'false'));
            
            $nomDir = $nomDevis . '_' .$token;
            error_log("- nomDir calculé: " . $nomDir);
            
            if($isCertified){
                $pdfUrl = "storage/" . $client . "/devis/" . $nomDir . '/' .  $nomDir . "_certifie.pdf";
            }
            else{
                $pdfUrl = "storage/" . $client . "/devis/" . $nomDir . '/' .  $nomDir . ".pdf"; 
            }
            
            error_log("- pdfUrl construit: " . $pdfUrl);
            
            // Vérification de l'existence physique du fichier
            $fullPath = public_path($pdfUrl);
            error_log("- Chemin physique complet: " . $fullPath);
            error_log("- Fichier existe: " . (file_exists($fullPath) ? 'OUI' : 'NON'));
            
            // Vérification avec asset()
            $assetUrl = asset($pdfUrl);
            error_log("- URL avec asset(): " . $assetUrl);
            
            // Test du contenu du répertoire parent si possible
            $parentDir = public_path("storage/" . $client . "/devis/" . $nomDir);
            if (is_dir($parentDir)) {
                $files = scandir($parentDir);
                error_log("- Contenu du répertoire: " . implode(', ', array_diff($files, ['.', '..'])));
            } else {
                error_log("- Répertoire parent n'existe pas: " . $parentDir);
            }
            
            error_log("=== FIN DEBUG VUE ===");
        ?>
        @if ($isAndroid)
            <div class="download-container">
                <a href="{{ asset($pdfUrl) }}" class="btn btn-primary btn-lg">
                    📥 Télécharger le document au format PDF
                </a>
                <!-- Debug visible pour développement -->
                <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; font-size: 12px; text-align: left;">
                    <strong>Debug Info:</strong><br>
                    URL PDF: {{ $pdfUrl }}<br>
                    Asset URL: {{ asset($pdfUrl) }}<br>
                    Certified: {{ $isCertified ? 'Oui' : 'Non' }}
                </div>
            </div>
        @else
            <div class="pdf-container">
                <iframe src="{{ asset($pdfUrl) }}" style="width:100%; height:100%;">
                    Votre navigateur ne permet pas l'affichage de PDF. 
                    <a href="{{ asset($pdfUrl) }}">📥 Télécharger le document au format PDF</a>
                </iframe>
                <!-- Debug visible pour développement -->
                <div style="position: absolute; top: 10px; right: 10px; padding: 10px; background: rgba(248,249,250,0.9); border: 1px solid #dee2e6; font-size: 11px; max-width: 300px;">
                    <strong>Debug Info:</strong><br>
                    URL PDF: {{ $pdfUrl }}<br>
                    Asset URL: {{ asset($pdfUrl) }}<br>
                    Certified: {{ $isCertified ? 'Oui' : 'Non' }}
                </div>
            </div>
        @endif
        
    </body>
</html>