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
                const pdfUrl = "{{ url('download-devis/' . $token) }}";
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
