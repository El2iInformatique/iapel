<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature électronique - APEL - EL2i informatique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #0f0f1a;
            --card-bg: #1a1a2e;
            --card-bg-light: #16213e;
            --text-primary: #e4e6ea;
            --text-secondary: #9ca3af;
            --border-color: #2d3748;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 25% 25%, #667eea22 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, #764ba222 0%, transparent 50%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .modern-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }

        .card-header-modern {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.25rem;
            text-align: center;
            border: none;
        }

        .alert-modern {
            background: var(--card-bg-light);
            border: 1px solid #667eea;
            border-radius: 15px;
            color: var(--text-primary);
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .list-group-modern {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .list-group-item-modern {
            background: var(--card-bg-light);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        .list-group-item-modern:hover {
            background: var(--card-bg);
            transform: translateX(5px);
        }

        .list-group-item-modern.total {
            background: var(--success-gradient);
            color: white;
            font-weight: 600;
        }

        .btn-modern {
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        }

        .btn-secondary-modern {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-secondary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        .signature-container {
            border-radius: 15px;
            border: 2px dashed var(--border-color);
            background: var(--card-bg-light);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .signature-container:hover {
            border-color: #667eea;
            background: var(--card-bg);
        }

        #signature-pad {
            border-radius: 10px;
            background: white;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-label-modern {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .modal-content-modern {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: var(--text-primary);
        }

        .modal-header-modern {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-icon {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .glass-effect {
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="modern-card fade-in">
                    <div class="card-header-modern">
                        <i class="bi bi-pen-fill me-2 pulse-icon"></i>
                        Signature du devis n°{{ $devis_id }}
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="mb-4">
                            <!-- Texte explicatif -->
                            <p class="text-center mb-4" style="color: var(--text-secondary); line-height: 1.6;">
                                Cette interface vous permet de <strong style="color: var(--text-primary);">signer électroniquement</strong> votre devis et de donner votre accord en toute simplicité.
                                Veuillez vérifier les informations ci-dessous avant de procéder à la signature.
                            </p>
          
                            <!-- Alert pour mettre en avant le titre du devis -->
                            <div class="alert-modern text-center fw-bold d-flex align-items-center justify-content-center mb-4">
                                <i class="bi bi-file-earmark-text me-2" style="font-size: 1.5rem;"></i>
                                <span>{{ $titre }}</span>
                            </div>

                            <ul class="list-group list-group-modern shadow-lg mb-4">
                                <li class="list-group-item-modern d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-receipt me-2"></i> Montant HT :</span>
                                    <strong style="color: #4facfe;">{{ number_format($montant_HT, 2, ',', ' ') }} €</strong>
                                </li>
                                <li class="list-group-item-modern d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-percent me-2"></i> Montant TVA :</span>
                                    <strong style="color: #f5576c;">{{ number_format($montant_TVA, 2, ',', ' ') }} €</strong>
                                </li>
                                <li class="list-group-item-modern total d-flex justify-content-between align-items-center">
                                    <span><strong><i class="bi bi-cash-coin me-2"></i> Total TTC :</strong></span>
                                    <strong style="font-size: 1.2rem;">{{ number_format($montant_TTC, 2, ',', ' ') }} €</strong>
                                </li>
                            </ul>
                            
                            <div class="mb-4">
                                <button id="viewPdfBtn" class="btn btn-modern btn-primary-modern w-100 py-3">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> Voir le devis
                                </button>
                            </div>
                        </div>

                        <hr style="border-color: var(--border-color); margin: 2rem 0;">

                        <div class="signature-container">     
                            <label class="form-label-modern">
                                <i class="bi bi-signature me-2"></i>
                                Signature du client :
                            </label>
                            <canvas id="signature-pad" class="border-0 w-100" style="height: 300px;"></canvas>
                            <input type="hidden" name="signature" id="signature">
                            <div class="d-flex gap-3 mt-3">
                                <button type="button" class="btn btn-modern btn-secondary-modern flex-fill" id="clear-signature">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Effacer
                                </button>
                                <button type="button" class="btn btn-modern btn-success-modern flex-fill" id="submit-signature">
                                    <i class="bi bi-check-circle me-2"></i>Signer électroniquement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap pour l'affichage du PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="pdfModalLabel">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Aperçu du Devis
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfViewer" src="" width="100%" height="740px" style="border: none; border-radius: 0 0 20px 20px;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'information -->
    <div class="modal fade" id="info_modal_signature" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="infoModalLabel">
                        <i class="bi bi-hourglass-split me-2"></i>Signature en cours
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Veuillez patienter pendant la génération de la signature de votre devis</p>
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
            // Créer la signature avec gestion souris + tactile
            let signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)', // Fond transparent
                penColor: "black", // Couleur du stylo
                backgroundColor: "white"
            });

            function resizeCanvas() {
                let ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
            }
            
            // Désactiver le resize après le premier chargement
            window.addEventListener("load", function () {
                resizeCanvas(); // Applique le redimensionnement une seule fois
                signaturePad.clear();
            });

            // Sauvegarder la signature au format Base64
            function saveSignature() {
                if (signaturePad.isEmpty()) {
                    alert("Veuillez signer avant de soumettre.");
                    return;
                }
                
                
                const infoModal_signature = new bootstrap.Modal(document.getElementById("info_modal_signature"));
                infoModal_signature.show();

                var signature = signaturePad.toDataURL("image/png");

                fetch("{{ url('/signature/' . $token) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ signature: signature })
                })
                .then(() => {
                    window.location.reload(); // ✅ Recharge la page après succès
                })
                .catch(error => {
                    console.error("Erreur :", error);
                });
            }

            // Empêcher le resize intempestif en bloquant les événements sur mobile
            canvas.addEventListener("touchstart", function () {
                window.removeEventListener("resize", resizeCanvas);
            }, { passive: false });

            // Effacer la signature
            clearButton.addEventListener("click", function () {
                signaturePad.clear();
                signatureInput.value = "";
            });

            
            // Signer le document
            submitButton.addEventListener("click", function () {
                saveSignature();
            });
            
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

        });
    </script>
</body>
</html>
