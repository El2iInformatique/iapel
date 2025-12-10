<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Devis non signé</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-y: auto;
        }
        .pdf-container {
            max-width: 900px;
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            height: 1230px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .message {
            font-size: 2rem;
            color: #666;
            font-weight: 300;
        }
        </style>
    </head>
    <body>
        <div class="pdf-container">
            <div class="message">Devis non signé</div>
        </div>
    </body>
</html>
