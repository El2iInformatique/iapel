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
            $isAndroid = stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
            $nomDir = $nomDevis . '_' .$token;
            if($isCertified){
                $pdfUrl = "storage/" . $client . "/devis/" . $nomDir . '/' .  $nomDir . "_certifie.pdf";
            }
            else{
                $pdfUrl = "storage/" . $client . "/devis/" . $nomDir . '/' .  $nomDir . ".pdf"; 
            }
            
        ?>
        @if ($isAndroid)
            <div class="download-container">
                <a href="{{ asset($pdfUrl) }}" class="btn btn-primary btn-lg">
                    📥 Télécharger le document au format PDF
                </a>
            </div>
        @else
            <div class="pdf-container">
                <iframe src="{{ asset($pdfUrl) }}" style="width:100%; height:100%;">
                    Votre navigateur ne permet pas l'affichage de PDF. 
                    <a href="{{ asset($pdfUrl) }}">📥 Télécharger le document au format PDF</a>
                </iframe>
            </div>
        @endif
        
    </body>
</html>