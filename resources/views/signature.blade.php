<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature électronique - APEL - EL2i informatique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Signature du devis n°{{ $devis_id }}</div>
                    
                    <div class="card-body">
                        
                        <div class="mb-4">
                            <!-- Texte explicatif -->
                            <p class="text-muted text-center">
                                Cette interface vous permet de <strong>signer électroniquement</strong> votre devis et de donner votre accord en toute simplicité.
                                Veuillez vérifier les informations ci-dessous avant de procéder à la signature.
                            </p>
          
                            <!-- Alert pour mettre en avant le titre du devis -->
                            <div class="alert alert-primary text-center fw-bold d-flex align-items-center justify-content-center">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                <span class="text-dark">{{ $titre }}</span>
                            </div>

                            <ul class="list-group shadow-sm">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="bi bi-receipt"></i> Montant HT :</span>
                                    <strong>{{ number_format($montant_HT, 2, ',', ' ') }} €</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="bi bi-percent"></i> Montant TVA :</span>
                                    <strong>{{ number_format($montant_TVA, 2, ',', ' ') }} €</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between bg-light">
                                    <span><strong><i class="bi bi-cash-coin"></i> Total TTC :</strong></span>
                                    <strong class="text-info">{{ number_format($montant_TTC, 2, ',', ' ') }} €</strong>
                                </li>
                            </ul>
                            <div class="mt-3">
                                <button id="viewPdfBtn" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-file-earmark-pdf"></i> Voir le devis
                                </button>
                            </div>
                        </div>

                        <hr>

                        <div class="col">     
                            <label class="form-label">Signature du client :</label>
                            <canvas id="signature-pad" class="border" style="width: 100%; height: 300px;"></canvas>
                            <input type="hidden" name="signature" id="signature">
                            <button type="button" class="btn btn-secondary mt-2" id="clear-signature">Effacer</button>
                            <button type="button" class="btn btn-success mt-2" id="submit-signature">Signer électroniquement</button>
                        </div>
                    </div>
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
                    <h5 class="modal-title" id="infoModalLabel">Signature en cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    Veuillez patienter pendant la génération de la signature de votre devis
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
