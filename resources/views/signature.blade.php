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
        /* Style demandé : Couleurs pleines (état hover) par défaut pour le bouton Refuser */
        .btn-refuse-permanent {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            border: 2px solid #dc3545 !important;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s ease;
        }
        .btn-refuse-permanent:hover {
            opacity: 0.85; /* Léger effet au survol pour garder une interactivité */
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    @php
        // Logique de refus : on vérifie si l'URL contient ?refused=1
        $isRefused = $refused ?? false;
        // Le devis est réellement signable s'il est valide ($signable) ET non refusé
        $actualSignable = $signable && !$isRefused;
    @endphp

    <div class="main-wrapper">
        <div class="signature-container">
            <div class="header-section">
                <div class="header-content">
                    @if($actualSignable)
                        <div class="status-badge">
                            <i class="bi bi-pen"></i>
                            <span>En attente de signature</span>
                        </div>
                    @elseif($isRefused)
                        <div class="status-badge" style="background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.4);">
                            <i class="bi bi-x-octagon"></i>
                            <span style="color: #ffffff;">Devis refusé</span>
                        </div>
                    @else
                        <div class="status-badge" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4);">
                            <i class="bi bi-x-circle"></i>
                            <span>Devis expiré</span>
                        </div>
                    @endif
                    <h1 class="header-title">Signature électronique</h1>
                    <div class="header-subtitle">Devis n°{{ $devis_id }}</div>
                </div>
            </div>

            <div class="content-section">
                @if($isRefused)
                    <div class="info-alert" style="background: #fee2e2; border-color: #ef4444;">
                        <div class="info-icon" style="background: #ef4444;">
                            <i class="bi bi-x-lg" style="color: white;"></i>
                        </div>
                        <div class="info-content">
                            <h3 style="color: #ef4444;">Devis refusé</h3>
                            <p>Vous avez refusé ce devis. L'option de signature électronique a été définitivement désactivée.</p>
                        </div>
                    </div>
                @elseif(!$signable)
                    <div class="info-alert" style="background: #fee2e2; border-color: #ef4444;">
                        <div class="info-icon" style="background: #ef4444;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="info-content">
                            <h3 style="color: #ef4444;">Devis expiré</h3>
                            <p>Ce devis n'est plus valide. La période de validité de 30 jours est dépassée. Veuillez contacter APEL - EL2i informatique pour obtenir un nouveau devis.</p>
                        </div>
                    </div>
                @else
                    <div class="info-alert" style="background: {{ $temps_restants <= 7 ? 'var(--warning-light)' : '#e0f2fe' }}; border-color: {{ $temps_restants <= 7 ? 'var(--warning-yellow)' : '#0ea5e9' }};">
                        <div class="info-icon" style="background: {{ $temps_restants <= 7 ? 'var(--warning-yellow)' : '#0ea5e9' }};">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="info-content">
                            <h3 style="color: {{ $temps_restants <= 7 ? 'var(--warning-yellow)' : '#0ea5e9' }};">
                                @if($temps_restants > 7)
                                    Devis valide - {{ $temps_restants }} jours restants
                                @elseif($temps_restants > 1)
                                    Attention - Plus que {{ $temps_restants }} jours pour signer
                                @else
                                    Dernier jour pour signer ce devis
                                @endif
                            </h3>
                            <p>Ce devis est valable pendant 30 jours à compter de sa date de création. 
                            @if($temps_restants <= 7)
                                <strong>Signez-le rapidement pour ne pas perdre cette offre.</strong>
                            @else
                                Vous pouvez le consulter et le signer à tout moment.
                            @endif
                            </p>
                        </div>
                    </div>
                @endif

                @if($actualSignable)
                    <div class="info-alert">
                        <div class="info-icon">
                            <i class="bi bi-info-lg"></i>
                        </div>
                        <div class="info-content">
                            <h3>Signature électronique sécurisée</h3>
                            <p>Cette interface vous permet de signer électroniquement votre devis et de donner votre accord en toute simplicité. Veuillez vérifier les informations ci-dessous avant de procéder à la signature.</p>
                        </div>
                    </div>
                @endif

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

                    <button id="viewPdfBtn" class="view-pdf-button" style="margin-bottom: 1rem;">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Consulter le devis complet</span>
                    </button>

                    @if($actualSignable)
                        <button type="button" id="btn-refuse-devis" class="btn btn-refuse-permanent w-100 p-3 d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="bi bi-trash3"></i>
                            <span>Refuser le devis</span>
                        </button>
                    @endif
                </div>

                <div class="signature-section">
                    <h3 class="signature-title">
                        <i class="bi bi-pen-fill"></i>
                        <span>Votre signature</span>
                    </h3>

                    <div class="signature-type-selector">
                        <button class="signature-type-btn active" data-target="#manual-signature">
                            <i class="bi bi-pencil"></i>
                            <span>Signature manuelle</span>
                        </button>
                        <button class="signature-type-btn" data-target="#fullname-signature">
                            <i class="bi bi-type"></i>
                            <span>Signature par nom et prénom</span>
                        </button>
                    </div>

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

                    <div id="fullname-signature" class="signature-method">
                        <div class="fullname-section">
                            <div class="fullname-input-group">
                                <div class="input-row">
                                    <div class="form-group">
                                        <label for="firstname" class="form-label">Prénom</label>
                                        <input type="text" id="firstname" class="form-input" placeholder="Ex: Jean" maxlength="20">
                                    </div>
                                    <div class="form-group">
                                        <label for="lastname" class="form-label">Nom</label>
                                        <input type="text" id="lastname" class="form-input" placeholder="Ex: Dupont" maxlength="30">
                                    </div>
                                </div>
                                <button type="button" class="generate-btn" id="generate-fullname" style="width: 100%;">
                                    <i class="bi bi-magic"></i>
                                    <span>Afficher la signature</span>
                                </button>
                            </div>
                            <div class="fullname-preview" id="fullname-preview">
                                <div class="preview-placeholder">Votre signature apparaîtra ici</div>
                            </div>
                            <div class="signature-controls">
                                <button type="button" class="clear-button" id="clear-fullname">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    <span>Effacer</span>
                                </button>
                                <button type="button" class="sign-button" id="submit-fullname">
                                    <i class="bi bi-check-lg"></i>
                                    <span>Signer électroniquement</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-branding">
                <div class="branding-content">
                    <div class="company-name">APEL - Solution de signature électronique</div>
                    <div class="developer-info">Développé par EL2i informatique - {{ $organisation_id }}</div>
                </div>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="info_modal_signature" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Signature en cours
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border text-primary me-3" role="status"></div>
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
            
            const infoModalElement = document.getElementById("info_modal_signature");
            const infoModal = new bootstrap.Modal(infoModalElement);

            // --- LOGIQUE COMMUNE (PDF) ---
            var pdfUrl = "{{ url('devis/' . $organisation_id . '/' . $devis_id ) }}";
            var pdfDownloadUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id ) }}";
            var viewPdfBtn = document.getElementById("viewPdfBtn");

            if (viewPdfBtn) {
                viewPdfBtn.addEventListener("click", function () {
                    if (window.innerWidth > 768) {
                        var pdfViewer = document.getElementById("pdfViewer");
                        pdfViewer.src = pdfUrl;
                        var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));
                        pdfModal.show();
                    } else {
                        window.location.href = pdfDownloadUrl;
                    }
                });
            }

            // --- LOGIQUE CONDITIONNELLE (Signature) ---
            const isSignable = {{ $actualSignable ? 'true' : 'false' }};
            const disableReason = "{{ $isRefused ? 'Devis refusé' : 'Devis expiré' }}";
            
            let canvas = document.getElementById("signature-pad");
            let submitButton = document.getElementById("submit-signature");
            let clearButton = document.getElementById("clear-signature");
            let placeholder = document.getElementById("signature-placeholder");
            let signaturePad;

            if (!isSignable) {
                // Désactivation des éléments si non signable
                if(canvas) { canvas.style.pointerEvents = 'none'; canvas.style.opacity = '0.5'; }
                if(submitButton) { submitButton.disabled = true; submitButton.innerHTML = `<span>${disableReason}</span>`; }
                // ... autres désactivations si nécessaire
            }

            // Initialisation Signature Pad
            if (isSignable && canvas) {
                function resizeCanvas() {
                    const rect = canvas.getBoundingClientRect();
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = rect.width * ratio;
                    canvas.height = rect.height * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    if (signaturePad) signaturePad.clear();
                }

                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 1)',
                    penColor: "#334155",
                    onBegin: () => { placeholder.classList.add("hidden"); }
                });

                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();

                // Submit Manuel
                submitButton.addEventListener("click", function () {
                    if (signaturePad.isEmpty()) return alert("Veuillez signer avant de soumettre.");
                    
                    submitButton.disabled = true;
                    infoModal.show();

                    fetch("{{ url('/signature/' . $token) }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ signature: signaturePad.toDataURL("image/png") })
                    })
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(() => window.location.reload())
                    .catch(() => {
                        alert("Erreur lors de la signature.");
                        submitButton.disabled = false;
                        infoModal.hide();
                    });
                });

                clearButton.addEventListener("click", () => {
                    signaturePad.clear();
                    placeholder.classList.remove("hidden");
                });
            }

            // Gestion Nom/Prénom
            if (isSignable) {
                const generateFullnameButton = document.getElementById('generate-fullname');
                const submitFullnameButton = document.getElementById('submit-fullname');
                const firstnameInput = document.getElementById('firstname');
                const lastnameInput = document.getElementById('lastname');
                const fullnamePreview = document.getElementById('fullname-preview');

                generateFullnameButton.addEventListener('click', () => {
                    const fn = firstnameInput.value.trim();
                    const ln = lastnameInput.value.trim();
                    if (fn && ln) {
                        fullnamePreview.innerHTML = `<div class="fullname-signature">${fn} ${ln}</div>`;
                        fullnamePreview.classList.add('has-signature');
                    }
                });

                // Submit Fullname (AJOUT DE LA POPUP ICI)
                submitFullnameButton.addEventListener('click', () => {
                    const fn = firstnameInput.value.trim();
                    const ln = lastnameInput.value.trim();
                    
                    if (!fn || !ln) return alert("Nom et prénom requis");
                    if (!fullnamePreview.classList.contains('has-signature')) return alert("Veuillez d'abord afficher la signature");

                    submitFullnameButton.disabled = true;
                    infoModal.show(); // Affiche la pop-up "Signature en cours"

                    fetch("{{ url('/signature-fullname/' . $token) }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ firstname: fn, lastname: ln })
                    })
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(() => window.location.reload())
                    .catch(() => {
                        alert("Erreur lors de la signature.");
                        submitFullnameButton.disabled = false;
                        infoModal.hide();
                    });
                });
            }

            // Sélecteur d'onglets Signature
            const typeButtons = document.querySelectorAll('.signature-type-btn');
            typeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    typeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const target = this.getAttribute('data-target');
                    document.querySelectorAll('.signature-method').forEach(m => {
                        m.classList.toggle('active', `#${m.id}` === target);
                    });
                });
            });

            // Logique Refuser
            const btnRefuseDevis = document.getElementById('btn-refuse-devis');
            if (btnRefuseDevis) {
                btnRefuseDevis.addEventListener('click', function() {
                    if (confirm('Êtes-vous sûr de vouloir refuser ce devis ?')) {
                        btnRefuseDevis.disabled = true;
                        fetch("{{ url('/api/devis-refuse/' . $token) }}", {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                            body: JSON.stringify({ devis_id: "{{ $devis_id }}" })
                        })
                        .then(() => window.location.reload())
                        .catch(() => { btnRefuseDevis.disabled = false; });
                    }
                });
            }

            // Animations d'entrée
            const elements = document.querySelectorAll('.info-alert, .document-details, .signature-section');
            elements.forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, i * 150);
            });
        });
    </script>
</body>
</html>