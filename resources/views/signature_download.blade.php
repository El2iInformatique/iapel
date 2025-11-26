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

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #2c3e50;
            line-height: 1.6;
        }

        .main-container {
            padding: 2rem 0;
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
            max-width: 600px;
            margin: 0 auto;
        }

        .card-header-professional {
            background: var(--primary-color);
            color: var(--white);
            padding: 1.5rem 2rem;
            border-bottom: 3px solid var(--accent-color);
        }

        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .card-body-professional {
            padding: 2rem;
        }

        .status-notification {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem 1.25rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .status-icon {
            font-size: 1.25rem;
            color: var(--success-color);
        }

        .document-info {
            background: var(--light-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .document-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .financial-summary {
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .summary-header {
            background: var(--medium-gray);
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--medium-gray);
            background: var(--white);
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
            font-size: 0.95rem;
        }

        .summary-amount {
            font-weight: 600;
            font-size: 1rem;
        }

        .download-section {
            text-align: center;
            padding-top: 1rem;
        }

        .download-button {
            background: var(--success-color);
            border: none;
            color: var(--white);
            padding: 0.875rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 200px;
            justify-content: center;
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
            font-size: 0.85rem;
            margin-top: 0.75rem;
        }

        .company-branding {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--medium-gray);
            color: var(--dark-gray);
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .main-container {
                padding: 1rem 0;
            }
            
            .card-body-professional {
                padding: 1.5rem;
            }
            
            .header-title {
                font-size: 1.1rem;
            }
            
            .summary-item {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-12">
                <div class="document-card">
                    <div class="card-header-professional">
                        <h1 class="header-title">
                            <i class="bi bi-check-circle-fill"></i>
                            Signature électronique validée
                        </h1>
                        <div class="header-subtitle">Devis n°{{ $devis_id }}</div>
                    </div>
                    
                    <div class="card-body-professional">
                        <!-- Notification de succès -->
                        <div class="status-notification">
                            <i class="bi bi-check-circle-fill status-icon"></i>
                            <div>
                                <strong>Signature enregistrée avec succès</strong><br>
                                Votre document est maintenant disponible au téléchargement
                            </div>
                        </div>
                        
                        <!-- Informations du document -->
                        <div class="document-info">
                            <div class="document-title">
                                <i class="bi bi-file-earmark-text me-2"></i>{{ $titre }}
                            </div>
                            <small class="text-muted">
                                Document signé le {{ date('d/m/Y à H:i') }}
                            </small>
                        </div>

                        <!-- Récapitulatif financier -->
                        <div class="financial-summary">
                            <div class="summary-header">
                                <i class="bi bi-calculator me-2"></i>Récapitulatif financier
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i class="bi bi-receipt"></i>
                                    Montant hors taxes
                                </div>
                                <div class="summary-amount">{{ number_format($montant_HT, 2, ',', ' ') }} €</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i class="bi bi-percent"></i>
                                    TVA
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
                                Télécharger le devis signé
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
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Préparation...';
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
                button.innerHTML = '<i class="bi bi-check-lg"></i> Téléchargé';
                
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
