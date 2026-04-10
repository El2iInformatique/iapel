<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Accès Configuration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #6f42c1 0%, #4b2885 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="card shadow-lg border-0 login-card">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="bi bi-shield-lock-fill me-2"></i> Accès sécurisé</h4>
        </div>
        <div class="card-body p-4">
            
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <p class="text-muted text-center mb-4">Veuillez saisir votre clé API pour accéder aux paramètres du document <strong>{{ $document }}</strong>.</p>
            
            <form method="POST" action="/configuration/auth">
                @csrf
                <input type="hidden" name="entreprise" value="{{ $entreprise }}">
                <input type="hidden" name="document" value="{{ $document }}">

                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" name="api_key" class="form-control form-control-lg" placeholder="Entrez votre clé API" required autofocus autocomplete="off">
                </div>
                
                <button type="submit" class="btn btn-lg w-100 text-white shadow-sm" style="background-color: #6f42c1;">
                    Valider l'accès <i class="bi bi-arrow-right-circle ms-1"></i>
                </button>
            </form>

        </div>
    </div>

</body>
</html>