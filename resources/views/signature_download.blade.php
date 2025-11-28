<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature électronique - APEL - EL2i informatique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        :root {
            --primary-blue: #1e40af;
            --primary-dark: #1e3a8a;
            --success-green: #059669;
            --success-light: #d1fae5;
            --accent-orange: #ea580c;
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-300: #cbd5e1;
            --neutral-400: #94a3b8;
            --neutral-500: #64748b;
            --neutral-600: #475569;
            --neutral-700: #334155;
            --neutral-800: #1e293b;
            --neutral-900: #0f172a;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--neutral-50) 0%, var(--neutral-100) 100%);
            color: var(--neutral-800);
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-weight: 400;
        }

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .document-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            width: 100%;
            max-width: 800px;
            border: 1px solid var(--neutral-200);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            padding: 2rem 2.5rem;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }

        .header-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .content-section {
            padding: 2.5rem;
        }

        .success-alert {
            background: var(--success-light);
            border: 1px solid var(--success-green);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .success-icon {
            background: var(--success-green);
            color: var(--white);
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .success-content h3 {
            color: var(--success-green);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }

        .success-content p {
            color: var(--neutral-600);
            margin: 0;
            font-size: 0.9375rem;
        }

        .document-details {
            background: var(--neutral-50);
            border: 1px solid var(--neutral-200);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .document-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .document-icon {
            background: var(--primary-blue);
            color: var(--white);
            width: 3rem;
            height: 3rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .document-info h4 {
            color: var(--neutral-800);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            line-height: 1.4;
        }

        .document-meta {
            color: var(--neutral-500);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .financial-summary {
            background: var(--white);
            border: 1px solid var(--neutral-200);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow-sm);
        }

        .summary-header {
            background: var(--neutral-100);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--neutral-200);
        }

        .summary-title {
            color: var(--neutral-800);
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--neutral-100);
            transition: background-color 0.15s ease;
        }

        .summary-row:hover {
            background: var(--neutral-50);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            background: var(--primary-blue);
            color: var(--white);
            font-weight: 600;
            font-size: 1.0625rem;
        }

        .summary-row.total:hover {
            background: var(--primary-dark);
        }

        .summary-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9375rem;
            color: var(--neutral-600);
        }

        .summary-row.total .summary-label {
            color: var(--white);
        }

        .summary-amount {
            font-weight: 600;
            font-size: 1rem;
            color: var(--neutral-800);
            text-align: right;
        }

        .summary-row.total .summary-amount {
            color: var(--white);
            font-size: 1.125rem;
        }

        .download-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .download-button {
            background: linear-gradient(135deg, var(--success-green) 0%, #047857 100%);
            border: none;
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            min-width: 280px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .download-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .download-button:active {
            transform: translateY(0);
        }

        .download-button:disabled {
            background: var(--neutral-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .download-info {
            margin-top: 1rem;
            color: var(--neutral-500);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .footer-branding {
            background: var(--neutral-50);
            padding: 1.5rem 2.5rem;
            border-top: 1px solid var(--neutral-200);
            text-align: center;
        }

        .branding-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .company-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .developer-info {
            font-size: 0.875rem;
            color: var(--neutral-500);
        }

        .loading-spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Animations d'entrée */
        .document-container {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-wrapper {
                padding: 1rem 0.5rem;
            }

            .header-section {
                padding: 1.5rem 1.5rem;
            }

            .header-title {
                font-size: 1.5rem;
            }

            .header-subtitle {
                font-size: 1rem;
            }

            .content-section {
                padding: 1.5rem;
            }

            .footer-branding {
                padding: 1.5rem;
            }

            .download-button {
                width: 100%;
                min-width: unset;
            }

            .summary-row {
                padding: 0.75rem 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .summary-amount {
                align-self: flex-end;
                font-size: 1.0625rem;
            }

            .document-header {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (min-width: 1200px) {
            .document-container {
                max-width: 900px;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="document-container">
            <!-- En-tête avec statut -->
            <div class="header-section">
                <div class="header-content">
                    <div class="status-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Signature validée</span>
                    </div>
                    <h1 class="header-title">Signature électronique</h1>
                    <div class="header-subtitle">Devis n°{{ $devis_id }}</div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="content-section">
                <!-- Notification de succès -->
                <div class="success-alert">
                    <div class="success-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div class="success-content">
                        <h3>Signature enregistrée avec succès</h3>
                        <p>Votre document a été signé électroniquement et est maintenant disponible au téléchargement en format PDF sécurisé.</p>
                    </div>
                </div>

                <!-- Détails du document -->
                <div class="document-details">
                    <div class="document-header">
                        <div class="document-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="document-info">
                            <h4>{{ $titre }}</h4>
                            <div class="document-meta">
                                <i class="bi bi-calendar3"></i>
                                <span>Signé le {{ date('d/m/Y à H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif financier -->
                <div class="financial-summary">
                    <div class="summary-header">
                        <h3 class="summary-title">
                            <i class="bi bi-calculator"></i>
                            <span>Récapitulatif financier</span>
                        </h3>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">
                            <i class="bi bi-receipt"></i>
                            <span>Montant hors taxes</span>
                        </div>
                        <div class="summary-amount">{{ number_format($montant_HT, 2, ',', ' ') }} €</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">
                            <i class="bi bi-percent"></i>
                            <span>Taxe sur la valeur ajoutée</span>
                        </div>
                        <div class="summary-amount">{{ number_format($montant_TVA, 2, ',', ' ') }} €</div>
                    </div>
                    <div class="summary-row total">
                        <div class="summary-label">
                            <i class="bi bi-cash-coin"></i>
                            <span>Total toutes taxes comprises</span>
                        </div>
                        <div class="summary-amount">{{ number_format($montant_TTC, 2, ',', ' ') }} €</div>
                    </div>
                </div>

                <!-- Section téléchargement -->
                <div class="download-section">
                    <button id="downloadBtn" class="download-button">
                        <i class="bi bi-download"></i>
                        <span>Télécharger le devis signé</span>
                    </button>
                    <div class="download-info">
                        <i class="bi bi-shield-check"></i>
                        <span>Format PDF avec signature électronique certifiée</span>
                    </div>
                </div>
            </div>

            <!-- Pied de page avec branding -->
            <div class="footer-branding">
                <div class="branding-content">
                    <div class="company-name">APEL - Solution de signature électronique</div>
                    <div class="developer-info">Développé par EL2i informatique</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("downloadBtn").addEventListener("click", function() {
            const button = this;
            const originalHTML = button.innerHTML;
            
            // État de chargement
            button.innerHTML = `
                <div class="loading-spinner"></div>
                <span>Préparation du téléchargement...</span>
            `;
            button.disabled = true;
            
            // Simulation du processus de génération
            setTimeout(() => {
                // Création du lien de téléchargement
                const pdfUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
                const link = document.createElement("a");
                link.href = pdfUrl;
                link.download = "Devis_{{ $devis_id }}_signe.pdf";
                link.target = "_blank";
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // État de succès
                button.innerHTML = `
                    <i class="bi bi-check-lg"></i>
                    <span>Téléchargement lancé</span>
                `;
                
                // Retour à l'état initial
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 3000);
                
            }, 1200);
        });

        // Animation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Ajout d'une classe pour l'animation de fade-in des éléments
            const elements = document.querySelectorAll('.success-alert, .document-details, .financial-summary, .download-section');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>
