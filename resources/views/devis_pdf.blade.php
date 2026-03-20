<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    <style>
        /* On force le html et le body à prendre toute la place et on retire les marges */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Empêche le double scroll éventuel */
        }

        /* Conteneur plein écran */
        .pdf-container {
            width: 100%;
            height: 100vh; /* 100% de la hauteur de la vue */
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Style optionnel pour le bouton Android pour qu'il soit centré */
        .download-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
</head>
<body>
    <?php
        $isAndroid = stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
        $nomDir = $nomDevis;
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
            <iframe src="{{ asset($pdfUrl) }}">
                Votre navigateur ne permet pas l'affichage de PDF. 
                <a href="{{ asset($pdfUrl) }}">📥 Télécharger le document au format PDF</a>
            </iframe>
        </div>
    @endif
</body>
</html>