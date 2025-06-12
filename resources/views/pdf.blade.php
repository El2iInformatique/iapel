<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fiche d'intervention cerfa_15497 n°{{ $uid }}</title>
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
            $route = 'generate-'.$document;
            $pdfUrl = "/storage/" . urlencode($client) . "/" . urlencode($document) . "/" . urlencode($uid) . "/" . urlencode($uid) . ".pdf";        
        ?>
        @if($isAndroid)
            <div class="download-container">
                <div class="alert alert-success" id="pdf-message">
                    ✅ Le document <strong>{{ $document }}</strong> n°<strong>{{ $uid }}</strong> est en cours de génération...
                </div>
            </div>

            <script>
                // Lancer la génération du PDF via une requête AJAX
                
                fetch("/{{ $route }}?client={{ urlencode($client) }}&document={{ urlencode($document) }}&uid={{ urlencode($uid) }}")
                    .then(response => {
                        if (response.ok) {
                            
                            // Modifier le message pour indiquer que le PDF est prêt
                            document.getElementById('pdf-message').innerHTML = 
                                "✅ Le document <strong>{{ $document }}</strong> n°<strong>{{ $uid }}</strong> a été généré avec succès !";

                        } else {
                            document.getElementById('pdf-message').innerHTML = "❌ Une erreur est survenue lors de la génération du PDF.";
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la génération du PDF:', error);
                        document.getElementById('pdf-message').innerHTML = "❌ Une erreur est survenue lors de la génération du PDF.";
                    });
            </script>
        @else    
            <div class="pdf-container">
                <iframe src="/<?php echo $route; ?>?client={{ urlencode($client) }}&document={{ urlencode($document) }}&uid={{ urlencode($uid) }}" style="width:100%; height:100%;"></iframe>
            </div>
        @endif
    </body>
</html>
