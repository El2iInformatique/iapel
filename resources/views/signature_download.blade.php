<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature électronique - APEL - EL2i informatique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #27ae60;
            --accent-color: #3498db;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #2c3e50;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .main-container {
            padding: 1rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .document-card {
            background: var(--white);
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .card-header-professional {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem 1.5rem;
            border-bottom: 3px solid var(--accent-color);
        }

        .header-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .header-subtitle {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .card-body-professional {
            padding: 1.5rem;
        }

        .status-notification {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .status-icon {
            font-size: 1.25rem;
            color: var(--success-color);
            flex-shrink: 0;
        }

        .status-content {
            flex: 1;
            min-width: 0;
        }

        .document-info {
            background: var(--light-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .document-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .financial-summary {
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .summary-header {
            background: var(--medium-gray);
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--medium-gray);
            background: var(--white);
            gap: 1rem;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item.total {
            background: var(--primary-color);
            color: var(--white);
            font-weight: 600;
        }

        .summary-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            flex: 1;
            min-width: 0;
        }

        .summary-amount {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .download-section {
            text-align: center;
            padding-top: 1rem;
        }

        .download-button {
            background: var(--success-color);
            border: none;
            color: var(--white);
            padding: 1rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 300px;
        }

        .download-button:hover {
            background: #219a52;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.2);
        }

        .download-button:active {
            transform: translateY(0);
        }

        .download-button:disabled {
            background: var(--dark-gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .download-info {
            color: var(--dark-gray);
            font-size: 0.8rem;
            margin-top: 0.75rem;
            text-align: center;
        }

        .company-branding {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--medium-gray);
            color: var(--dark-gray);
            font-size: 0.8rem;
            line-height: 1.4;
        }

        /* Breakpoints responsifs */
        @media (max-width: 767px) {
            .main-container {
                padding: 0.5rem;
                align-items: flex-start;
                padding-top: 2rem;
            }
            
            .card-body-professional {
                padding: 1rem;
            }
            
            .card-header-professional {
                padding: 1rem;
            }
            
            .header-title {
                font-size: 1rem;
                gap: 0.4rem;
            }
            
            .header-subtitle {
                font-size: 0.8rem;
            }
            
            .summary-item {
                padding: 0.6rem 0.8rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .summary-item .summary-label {
                width: 100%;
            }
            
            .summary-item .summary-amount {
                align-self: flex-end;
                font-size: 1rem;
            }
            
            .document-title {
                font-size: 0.95rem;
            }
            
            .status-notification {
                padding: 0.8rem;
                flex-direction: column;
                text-align: center;
            }
            
            .download-button {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
            
            .company-branding {
                margin-top: 1.5rem;
                padding-top: 1rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0.25rem;
                padding-top: 1rem;
            }
            
            .document-card {
                border-radius: 4px;
            }
            
            .card-body-professional {
                padding: 0.8rem;
            }
            
            .card-header-professional {
                padding: 0.8rem;
            }
            
            .header-title {
                font-size: 0.9rem;
                text-align: center;
                justify-content: center;
            }
            
            .summary-header {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }
            
            .summary-item {
                padding: 0.5rem 0.6rem;
            }
            
            .summary-label {
                font-size: 0.85rem;
            }
            
            .summary-amount {
                font-size: 0.9rem;
            }
            
            .download-info {
                font-size: 0.75rem;
            }
        }

        @media (min-width: 768px) {
            .main-container {
                padding: 2rem;
            }
            
            .card-body-professional {
                padding: 2rem;
            }
            
            .card-header-professional {
                padding: 1.5rem 2rem;
            }
            
            .header-title {
                font-size: 1.25rem;
            }
            
            .summary-item {
                padding: 0.875rem 1.25rem;
            }
            
            .download-button {
                max-width: 250px;
                padding: 0.875rem 2rem;
            }
        }

        @media (min-width: 992px) {
            .document-card {
                max-width: 650px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-6">
                <div class="document-card">
                    <div class="card-header-professional">
                        <h1 class="header-title">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Signature électronique validée</span>
                        </h1>
                        <div class="header-subtitle">Devis n°{{ $devis_id }}</div>
                    </div>
                    
                    <div class="card-body-professional">
                        <!-- Notification de succès -->
                        <div class="status-notification">
                            <i class="bi bi-check-circle-fill status-icon"></i>
                            <div class="status-content">
                                <strong>Signature enregistrée avec succès</strong><br>
                                Votre document est maintenant disponible au téléchargement
                            </div>
                        </div>
                        
                        <!-- Informations du document -->
                        <div class="document-info">
                            <div class="document-title">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>{{ $titre }}</span>
                            </div>
                            <small class="text-muted">
                                Document signé le {{ date('d/m/Y à H:i') }}
                            </small>
                        </div>

                        <!-- Récapitulatif financier -->
                        <div class="financial-summary">
                            <div class="summary-header">
                                <i class="bi bi-calculator"></i>
                                <span>Récapitulatif financier</span>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i class="bi bi-receipt"></i>
                                    <span>Montant hors taxes</span>
                                </div>
                                <div class="summary-amount">{{ number_format($montant_HT, 2, ',', ' ') }} €</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i class="bi bi-percent"></i>
                                    <span>TVA</span>
                                </div>
                                <div class="summary-amount">{{ number_format($montant_TVA, 2, ',', ' ') }} €</div>
                            </div>
                            <div class="summary-item total">
                                <div class="summary-label">
                                    <i class="bi bi-cash-coin"></i>
                                    <strong>Total TTC</strong>
                                </div>
                                <div class="summary-amount">{{ number_format($montant_TTC, 2, ',', ' ') }} €</div>
                            </div>
                        </div>

                        <!-- Section téléchargement -->
                        <div class="download-section">
                            <button id="viewPdfBtn" class="download-button">
                                <i class="bi bi-download"></i>
                                <span>Télécharger le devis signé</span>
                            </button>
                            <div class="download-info">
                                <i class="bi bi-info-circle me-1"></i>
                                Format PDF - Signature électronique certifiée
                            </div>
                        </div>

                        <!-- Branding entreprise -->
                        <div class="company-branding">
                            <div><strong>APEL</strong> - Solution de signature électronique</div>
                            <div>Développé par <strong>EL2i informatique</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("viewPdfBtn").addEventListener("click", function() {
            const button = this;
            const originalContent = button.innerHTML;
            
            // Changement d'état du bouton
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Préparation...</span>';
            button.disabled = true;
            
            // Simulation d'un délai de traitement
            setTimeout(() => {
                const pdfUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
                const link = document.createElement("a");
                link.href = pdfUrl;
                link.download = "{{ $devis_id }}.pdf";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Confirmation du téléchargement
                button.innerHTML = '<i class="bi bi-check-lg"></i> <span>Téléchargé</span>';
                
                // Retour à l'état initial après 2 secondes
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }, 2000);
            }, 800);
        });
    </script>
</body>
</html>
