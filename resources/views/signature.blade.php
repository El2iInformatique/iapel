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
            --primary-purple: #8B5A96;
            --primary-dark: #6B4575;
            --primary-light: #A478B0;
            --accent-orange: #F4A261;
            --accent-orange-dark: #E76F00;
            --success-green: #059669;
            --success-light: #d1fae5;
            --warning-yellow: #F59E0B;
            --warning-light: #FEF3C7;
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
            background: linear-gradient(135deg, var(--neutral-50) 0%, #f3f0f5 100%);
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

        .signature-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            border: 1px solid var(--neutral-200);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-dark) 100%);
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
            background: radial-gradient(circle, rgba(244,162,97,0.2) 0%, transparent 70%);
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
            border: 1px solid rgba(255, 255, 255, 0.3);
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

        .info-alert {
            background: var(--warning-light);
            border: 1px solid var(--warning-yellow);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-icon {
            background: var(--warning-yellow);
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

        .info-content h3 {
            color: var(--warning-yellow);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }

        .info-content p {
            color: var(--neutral-600);
            margin: 0;
            font-size: 0.9375rem;
        }

        .document-details {
            background: linear-gradient(135deg, #faf9fc 0%, var(--neutral-50) 100%);
            border: 1px solid #e8e5eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .document-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .document-icon {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-light) 100%);
            color: var(--white);
            width: 3rem;
            height: 3rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(139, 90, 150, 0.3);
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
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .summary-header {
            background: linear-gradient(135deg, #f8f6fa 0%, var(--neutral-100) 100%);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e8e5eb;
        }

        .summary-title {
            color: var(--primary-purple);
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
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-dark) 100%);
            color: var(--white);
            font-weight: 600;
            font-size: 1.0625rem;
        }

        .summary-row.total:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #5a3562 100%);
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

        .view-pdf-button {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-dark) 100%);
            border: none;
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(139, 90, 150, 0.4);
        }

        .view-pdf-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 90, 150, 0.5);
        }

        .signature-section {
            background: var(--white);
            border: 1px solid var(--neutral-200);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .signature-title {
            color: var(--neutral-800);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Nouveaux styles pour le système de double signature */
        .signature-type-selector {
            display: flex;
            background: var(--neutral-100);
            border-radius: 12px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            gap: 0.5rem;
        }

        .signature-type-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--neutral-600);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .signature-type-btn.active {
            background: var(--white);
            color: var(--primary-purple);
            box-shadow: var(--shadow-sm);
            font-weight: 600;
        }

        .signature-type-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
            color: var(--neutral-700);
        }

        .signature-method {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .signature-method.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Styles pour la signature par initiales */
        .initials-section {
            background: linear-gradient(135deg, #faf9fc 0%, var(--neutral-50) 100%);
            border: 1px solid #e8e5eb;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1rem;
        }

        .initials-input-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .input-row {
            display: flex;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            flex: 1;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--neutral-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--neutral-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(139, 90, 150, 0.1);
        }

        .generate-btn {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-dark) 100%);
            border: none;
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            height: fit-content;
        }

        .generate-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 90, 150, 0.4);
        }

        .initials-preview {
            background: var(--white);
            border: 2px dashed var(--neutral-300);
            border-radius: 12px;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .initials-preview.has-signature {
            border-color: var(--success-green);
            border-style: solid;
            background: #f0fdf4;
        }

        .initials-signature {
            font-family: 'Brush Script MT', cursive, 'Dancing Script', cursive;
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-purple);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            letter-spacing: 2px;
            transform: rotate(-5deg);
        }

        .preview-placeholder {
            color: var(--neutral-400);
            text-align: center;
            font-size: 0.875rem;
        }

        .signature-canvas {
            border: 2px dashed var(--neutral-300);
            border-radius: 12px;
            background: var(--neutral-50);
            cursor: crosshair;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            position: relative;
        }

        .signature-canvas:hover {
            border-color: var(--primary-purple);
            background: #faf9fc;
        }

        .signature-canvas.active {
            border-color: var(--primary-purple);
            border-style: solid;
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(139, 90, 150, 0.1);
        }

        .signature-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--neutral-400);
            font-size: 0.875rem;
            text-align: center;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .signature-placeholder.hidden {
            opacity: 0;
        }

        .signature-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .clear-button {
            background: var(--neutral-100);
            border: 1px solid var(--neutral-300);
            color: var(--neutral-600);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
        }

        .clear-button:hover {
            background: var(--neutral-200);
            border-color: var(--neutral-400);
        }

        .sign-button {
            background: linear-gradient(135deg, var(--success-green) 0%, #047857 100%);
            border: none;
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 2;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
        }

        .sign-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
        }

        .sign-button:disabled {
            background: var(--neutral-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        /* Responsive pour les nouvelles fonctionnalités */
        @media (max-width: 768px) {
            .signature-type-selector {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .input-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            
            .generate-btn {
                width: 100%;
                justify-content: center;
            }
            
            .initials-signature {
                font-size: 2rem;
            }
        }

        .footer-branding {
            background: linear-gradient(135deg, #f8f6fa 0%, var(--neutral-100) 100%);
            padding: 1.5rem 2.5rem;
            border-top: 1px solid #e8e5eb;
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
            color: var(--primary-purple);
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

        .signature-container {
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

            .signature-section {
                padding: 1.5rem;
            }

            .signature-controls {
                flex-direction: column;
            }

            .clear-button,
            .sign-button {
                flex: 1;
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
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="signature-container">
            <!-- En-tête -->
            <div class="header-section">
                <div class="header-content">
                    <div class="status-badge">
                        <i class="bi bi-pen"></i>
                        <span>En attente de signature</span>
                    </div>
                    <h1 class="header-title">Signature électronique</h1>
                    <div class="header-subtitle">Devis n°{{ $devis_id }}</div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="content-section">
                <!-- Information -->
                <div class="info-alert">
                    <div class="info-icon">
                        <i class="bi bi-info-lg"></i>
                    </div>
                    <div class="info-content">
                        <h3>Signature électronique sécurisée</h3>
                        <p>Cette interface vous permet de signer électroniquement votre devis et de donner votre accord en toute simplicité. Veuillez vérifier les informations ci-dessous avant de procéder à la signature.</p>
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
                                <i class="bi bi-building"></i>
                                <span>APEL - EL2i informatique</span>
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

                    <button id="viewPdfBtn" class="view-pdf-button">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Consulter le devis complet</span>
                    </button>
                </div>

                <!-- Section signature -->
                <div class="signature-section">
                    <h3 class="signature-title">
                        <i class="bi bi-pen-fill"></i>
                        <span>Votre signature</span>
                    </h3>

                    <!-- Sélecteur de type de signature -->
                    <div class="signature-type-selector">
                        <button class="signature-type-btn active" data-target="#manual-signature">
                            <i class="bi bi-pencil"></i>
                            <span>Signature manuelle</span>
                        </button>
                        <button class="signature-type-btn" data-target="#initials-signature">
                            <i class="bi bi-type"></i>
                            <span>Signature par initiales</span>
                        </button>
                    </div>

                    <!-- Méthode de signature manuelle -->
                    <div id="manual-signature" class="signature-method active">
                        <div style="position: relative;">
                            <canvas id="signature-pad" class="signature-canvas" style="width: 100%; height: 200px;"></canvas>
                            <div id="signature-placeholder" class="signature-placeholder">
                                <i class="bi bi-pencil" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                Cliquez et dessinez votre signature dans cette zone
                            </div>
                        </div>
                        <input type="hidden" name="signature" id="signature">
                        <div class="signature-controls">
                            <button type="button" class="clear-button" id="clear-signature">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Effacer</span>
                            </button>
                            <button type="button" class="sign-button" id="submit-signature">
                                <i class="bi bi-check-lg"></i>
                                <span>Signer électroniquement</span>
                            </button>
                        </div>
                    </div>

                    <!-- Méthode de signature par initiales -->
                    <div id="initials-signature" class="signature-method">
                        <div class="initials-section">
                            <div class="initials-input-group">
                                <div class="input-row">
                                    <div class="form-group">
                                        <label for="initials" class="form-label">Vos initiales</label>
                                        <input type="text" id="initials" class="form-input" maxlength="3" placeholder="Ex: ABC">
                                    </div>
                                    <button type="button" class="generate-btn" id="generate-initials">
                                        <i class="bi bi-magic"></i>
                                        <span>Générer</span>
                                    </button>
                                </div>
                            </div>
                            <div class="initials-preview" id="initials-preview">
                                <div class="preview-placeholder">Vos initiales apparaîtront ici</div>
                            </div>
                            <div class="signature-controls">
                                <button type="button" class="clear-button" id="clear-initials">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    <span>Effacer</span>
                                </button>
                                <button type="button" class="sign-button" id="submit-initials">
                                    <i class="bi bi-check-lg"></i>
                                    <span>Signer électroniquement</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pied de page -->
            <div class="footer-branding">
                <div class="branding-content">
                    <div class="company-name">APEL - Solution de signature électronique</div>
                    <div class="developer-info">Développé par EL2i informatique</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap pour l'affichage du PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Aperçu du Devis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfViewer" src="" width="100%" height="740px" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'information -->
    <div class="modal fade" id="info_modal_signature" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Signature en cours
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <div class="loading-spinner me-3"></div>
                        <span>Veuillez patienter pendant la génération de la signature de votre devis...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let canvas = document.getElementById("signature-pad");
            let signatureInput = document.getElementById("signature");
            let clearButton = document.getElementById("clear-signature");
            let submitButton = document.getElementById("submit-signature");
            let placeholder = document.getElementById("signature-placeholder");
            let signaturePad;

            // Fonction pour redimensionner le canvas correctement
            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                canvas.style.width = rect.width + 'px';
                canvas.style.height = rect.height + 'px';
                
                const context = canvas.getContext("2d");
                context.scale(ratio, ratio);
                
                // Réinitialiser le SignaturePad après redimensionnement
                if (signaturePad) {
                    signaturePad.clear();
                }
            }

            // Initialiser le canvas et SignaturePad
            function initSignaturePad() {
                resizeCanvas();
                
                // Créer la signature avec gestion souris + tactile
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 1)', // Fond blanc
                    penColor: "#334155", // Couleur du stylo
                    minWidth: 1,
                    maxWidth: 2.5,
                    throttle: 16,
                    minDistance: 5,
                    // Événements corrects pour SignaturePad
                    onBegin: function() {
                        canvas.classList.add("active");
                        placeholder.classList.add("hidden");
                    },
                    onEnd: function() {
                        if (!signaturePad.isEmpty()) {
                            placeholder.classList.add("hidden");
                        }
                    }
                });
            }

            // Initialiser au chargement
            initSignaturePad();

            // Sauvegarder la signature au format Base64
            function saveSignature() {
                if (signaturePad.isEmpty()) {
                    // Animation d'erreur sur le canvas
                    canvas.style.borderColor = "#ef4444";
                    canvas.style.borderWidth = "3px";
                    setTimeout(() => {
                        canvas.style.borderColor = "";
                        canvas.style.borderWidth = "";
                    }, 2000);
                    
                    // Alert stylisée
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                    alertDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Veuillez signer avant de soumettre.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                    return;
                }
                
                // Désactiver le bouton et changer son état
                submitButton.innerHTML = `
                    <div class="loading-spinner"></div>
                    <span>Traitement en cours...</span>
                `;
                submitButton.disabled = true;
                
                const infoModal_signature = new bootstrap.Modal(document.getElementById("info_modal_signature"));
                infoModal_signature.show();

                try {
                    var signature = signaturePad.toDataURL("image/png");
                    console.log("Signature générée, taille:", signature.length);

                    fetch("{{ url('/signature/' . $token) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ signature: signature })
                    })
                    .then(response => {
                        console.log("Réponse reçue:", response.status);
                        if (!response.ok) {
                            throw new Error('Erreur réseau: ' + response.status);
                        }
                        return response.json().catch(() => {
                            // Si pas de JSON en retour, on considère que c'est OK
                            return { success: true };
                        });
                    })
                    .then(data => {
                        console.log("Données reçues:", data);
                        // État de succès
                        submitButton.innerHTML = `
                            <i class="bi bi-check-lg"></i>
                            <span>Signature validée !</span>
                        `;
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    })
                    .catch(error => {
                        console.error("Erreur lors de la signature :", error);
                        
                        // État d'erreur
                        submitButton.innerHTML = `
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Erreur de signature</span>
                        `;
                        submitButton.style.backgroundColor = "#ef4444";
                        
                        // Alert d'erreur
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                        errorDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                        errorDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Erreur lors de la signature. Veuillez réessayer.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(errorDiv);
                        
                        setTimeout(() => {
                            if (errorDiv.parentNode) {
                                errorDiv.remove();
                            }
                            // Restaurer le bouton
                            submitButton.innerHTML = `
                                <i class="bi bi-check-lg"></i>
                                <span>Signer électroniquement</span>
                            `;
                            submitButton.disabled = false;
                            submitButton.style.backgroundColor = "";
                        }, 5000);
                        
                        infoModal_signature.hide();
                    });
                } catch (error) {
                    console.error("Erreur lors de la génération de la signature :", error);
                    submitButton.innerHTML = `
                        <i class="bi bi-check-lg"></i>
                        <span>Signer électroniquement</span>
                    `;
                    submitButton.disabled = false;
                    infoModal_signature.hide();
                }
            }

            // Effacer la signature
            clearButton.addEventListener("click", function () {
                console.log("Effacement de la signature");
                signaturePad.clear();
                signatureInput.value = "";
                canvas.classList.remove("active");
                placeholder.classList.remove("hidden");
            });

            // Signer le document
            submitButton.addEventListener("click", function () {
                console.log("Tentative de signature");
                saveSignature();
            });
            
            // Gestion PDF
            var pdfUrl = "{{ url('devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
            var pdfDownloadUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
            var viewPdfBtn = document.getElementById("viewPdfBtn");

            viewPdfBtn.addEventListener("click", function () {
                if (window.innerWidth > 768) { // Si PC (écran large)
                    var pdfViewer = document.getElementById("pdfViewer");
                    pdfViewer.src = pdfUrl;
                    var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));
                    pdfModal.show();
                } else { // Si mobile
                    window.location.href = pdfDownloadUrl; // Téléchargement direct
                }
            });

            // Redimensionner le canvas si nécessaire (débounced)
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if (signaturePad && !signaturePad.isEmpty()) {
                        // Sauvegarder la signature avant redimensionnement
                        const imageData = signaturePad.toDataURL();
                        resizeCanvas();
                        signaturePad.fromDataURL(imageData);
                    } else {
                        resizeCanvas();
                    }
                }, 250);
            });

            // Animation au chargement de la page
            const elements = document.querySelectorAll('.info-alert, .document-details, .signature-section');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 150);
            });

            // Gestion du système de double signature
            const signatureTypeButtons = document.querySelectorAll('.signature-type-btn');
            const signatureMethods = document.querySelectorAll('.signature-method');

            signatureTypeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    signatureTypeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const target = this.getAttribute('data-target');
                    signatureMethods.forEach(method => {
                        if (method.id === target.substring(1)) {
                            method.classList.add('active');
                        } else {
                            method.classList.remove('active');
                        }
                    });
                });
            });

            // Gestion de la signature par initiales
            const initialsInput = document.getElementById('initials');
            const generateInitialsButton = document.getElementById('generate-initials');
            const initialsPreview = document.getElementById('initials-preview');
            const clearInitialsButton = document.getElementById('clear-initials');
            const submitInitialsButton = document.getElementById('submit-initials');

            generateInitialsButton.addEventListener('click', function () {
                const initials = initialsInput.value.trim().toUpperCase();
                if (initials) {
                    initialsPreview.innerHTML = `<div class="initials-signature">${initials}</div>`;
                    initialsPreview.classList.add('has-signature');
                } else {
                    initialsPreview.innerHTML = `<div class="preview-placeholder">Vos initiales apparaîtront ici</div>`;
                    initialsPreview.classList.remove('has-signature');
                }
            });

            clearInitialsButton.addEventListener('click', function () {
                initialsInput.value = '';
                initialsPreview.innerHTML = `<div class="preview-placeholder">Vos initiales apparaîtront ici</div>`;
                initialsPreview.classList.remove('has-signature');
            });

            submitInitialsButton.addEventListener('click', function () {
                const initials = initialsInput.value.trim().toUpperCase();
                if (!initials) {
                    alert('Veuillez entrer vos initiales avant de soumettre.');
                    return;
                }

                // Désactiver le bouton et changer son état
                submitInitialsButton.innerHTML = `
                    <div class="loading-spinner"></div>
                    <span>Traitement en cours...</span>
                `;
                submitInitialsButton.disabled = true;

                const infoModal_signature = new bootstrap.Modal(document.getElementById("info_modal_signature"));
                infoModal_signature.show();

                fetch("{{ url('/signature-initials/' . $token) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ initials: initials })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Données reçues:", data);
                    submitInitialsButton.innerHTML = `
                        <i class="bi bi-check-lg"></i>
                        <span>Signature validée !</span>
                    `;
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                })
                .catch(error => {
                    console.error("Erreur lors de la signature par initiales :", error);
                    submitInitialsButton.innerHTML = `
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Erreur de signature</span>
                    `;
                    submitInitialsButton.style.backgroundColor = "#ef4444";
                    setTimeout(() => {
                        submitInitialsButton.innerHTML = `
                            <i class="bi bi-check-lg"></i>
                            <span>Signer électroniquement</span>
                        `;
                        submitInitialsButton.disabled = false;
                        submitInitialsButton.style.backgroundColor = "";
                    }, 5000);
                    infoModal_signature.hide();
                });
            });

            // Debug - vérifier que tout est bien initialisé
            console.log("SignaturePad initialisé:", signaturePad);
            console.log("Canvas:", canvas);
            console.log("Boutons:", { clearButton, submitButton });
        });
    </script>
</body>
</html>
