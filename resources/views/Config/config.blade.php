<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Paramètres du document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">

    <style>
        .required-asterisk {
            color: red;
            margin-left: 0.25rem;
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="signature-container">
            
            <div class="header-section" style="position: relative;">
                {{-- Le bouton retour a été supprimé car la page s'ouvre dans un nouvel onglet --}}
                <div class="header-content">
                    <div class="status-badge">
                        <i class="bi bi-gear-fill"></i> Paramètres
                    </div>
                    <h1 class="header-title">Configuration du document</h1>
                    <p class="header-subtitle">
                        @if(isset($type) && $type == 'bi')
                            Rapport d'intervention (BI)
                        @elseif(isset($type) && $type == 'cerfa')
                            Formulaire CERFA 15497
                        @elseif(isset($type) && $type == 'devis')
                            Devis Client
                        @else
                            Paramètres généraux
                        @endif
                    </p>
                </div>
            </div>

            <div class="content-section">

                {{-- Affichage des messages de succès ou d'erreur lors de l'enregistrement --}}
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                {{-- Le formulaire d'enregistrement des paramètres --}}
                <form action="/configuration" method="POST">
                    @csrf
                    {{-- On passe les identifiants en caché pour le submit --}}
                    <input type="hidden" name="entreprise" value="{{ $entreprise ?? '' }}">
                    <input type="hidden" name="document" value="{{ $document ?? '' }}">

                    @if(isset($type) && $type == 'rapport_intervention')
                        <div class="info-alert mb-4">
                            <div class="info-icon">
                                <i class="bi bi-wrench-adjustable"></i>
                            </div>
                            <div class="info-content">
                                <h3>Paramètres - Fiche d'intervention</h3>
                                <p>Modifiez ici les options liées au BI.</p>
                            </div>
                        </div>

                        <div class="accordion" id="accordion_parametres_bi">
                            @include('partials.config.bi_layout')
                        </div>


                    @elseif(isset($type) && $type == 'cerfa_15497')
                        <div class="info-alert mb-4">
                            <div class="info-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="info-content">
                                <h3>Paramètres - CERFA 15497</h3>
                                <p>Modifiez ici les options liées à l'attestation de capacité.</p>
                            </div>
                        </div>

                        <div class="accordion" id="accordion_parametres_cerfa">
                            @include('partials.config.cerfa_layout')
                        </div>


                    @elseif(isset($type) && $type == 'devis')
                        <div class="info-alert mb-4">
                            <div class="info-icon">
                                <i class="bi bi-calculator"></i>
                            </div>
                            <div class="info-content">
                                <h3>Paramètres - Devis</h3>
                                <p>Modifiez ici les options de tarification ou de présentation du devis.</p>
                            </div>
                        </div>

                        <div class="accordion" id="accordion_parametres_devis">
                            @include('partials.config.devis_layout')
                        </div>


                    @else
                        <div class="alert alert-warning text-center mt-4">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>
                            Aucun type de document spécifié.
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="view-pdf-button">
                            <i class="bi bi-save"></i> Enregistrer les paramètres
                        </button>
                    </div>
                </form>

            </div>
            
            <div class="footer-branding">
                <p class="text-muted mb-0">Paramètres système</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>