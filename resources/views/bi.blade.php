
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Fiche d'intervention</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon principal -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="favicon-48x48.png">

    <!-- Icône pour Apple (iPhone/iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">

    <!-- Icônes pour Android et PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="android-chrome-512x512.png">
    
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
            <div class="header-section">
                <div class="header-content">
                    <div class="status-badge">
                        <i class="bi bi-file-earmark-text"></i> Intervention en cours
                    </div>
                    <h1 class="header-title">Fiche d'intervention</h1>
                    <p class="header-subtitle">Bi n°{{ $uid }}</p>
                </div>
            </div>

            <div class="content-section">
                <!-- Formulaire -->
                <form action="{{ route('bi.submit', ['token' => $token]) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="info-alert">
                        <div class="info-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div class="info-content">
                            <h3>Description des travaux</h3>
                            <p><strong>{{ $data['description'] }}</strong></p>
                        </div>
                    </div>
                    
                    <div class="accordion" id="accordion_bi">
                        
                        <!-- Première section : information de l'intervention -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_information">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_information" aria-expanded="false" aria-controls="accordion_collapse_information">
                                    <i class="bi bi-info-square me-2"></i> 1 - Détail de l'intervention
                                </button>
                            </h2>
                            <div id="accordion_collapse_information" class="accordion-collapse collapse" aria-labelledby="accordion_header_information" data-bs-parent="#accordion_bi">
                                <div class="accordion-body">   
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="document-details h-100">
                                                <div class="document-header mb-2">
                                                    <div class="document-icon" style="width: 2rem; height: 2rem; font-size: 1rem;">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </div>
                                                    <div class="document-info">
                                                        <h4 class="mb-0">Lieu d'intervention</h4>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    {{ $data['adresse_intervention'] }}<br>
                                                    {{ $data['cp_intervention'] }} {{ $data['ville_intervention'] }} - {{ $data['lieu_intervention'] }}
                                                </div>
                                                <div class="mb-3">
                                                    <button type="button" class="generate-btn" onclick="ouvrirNavigation()">
                                                        <i class="bi bi-map"></i> Itinéraire
                                                    </button>
                                                </div>
                                                <hr>
                                                <div class="mb-3">    
                                                    <span class="text-muted">Intervenant :</span> <strong>{{ $data['intervenant'] }}</strong><br>
                                                    <span class="text-muted">Date prévue :</span>  <strong id="date-intervention-display"> {{ $data['date_intervention'] ?? date('d/m/Y') }} </strong>
                                                </div>
                                                <input type="hidden" id="date-intervention-hidden" name="date_intervention" value="{{ $data['date_intervention'] ?? date('d/m/Y') }}">
                                                <div class="mb-3">
                                                    <button type="button" class="generate-btn" onclick="modifierDateIntervention()">
                                                        <i class="bi bi-calendar"></i> Modifier la date
                                                    </button>
                                                </div> 
        
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="document-details h-100">
                                                <div class="document-header mb-2">
                                                    <div class="document-icon" style="width: 2rem; height: 2rem; font-size: 1rem;">
                                                        <i class="bi bi-receipt"></i>
                                                    </div>
                                                    <div class="document-info">
                                                        <h4 class="mb-0">Facturation</h4>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>{{ $data['nom_client'] }}</strong><br>
                                                    {{ $data['adresse_facturation'] }}<br>
                                                    {{ $data['cp_facturation'] }} {{ $data['ville_facturation'] }}<br>
                                                </div>
                                                <div class="mb-3"></div>
                                                <br>
                                                <hr>
                                                <div class="mb-3">
                                                    <span class="text-muted">Code client :</span> <strong>{{ $data['code_client'] }}</strong><br>
                                                    <span class="text-muted">Email :</span> <strong> <a href="mailto:{{ $data['email_client'] }}">{{ $data['email_client'] }}</a></strong><br>
                                                    <span class="text-muted">Téléphone :</span> <strong>{{ $data['telephone_client'] }}</strong><br>
                                                    <span class="text-muted">Portable :</span> <strong>{{ $data['portable_client'] }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Deuxième section : Réalisation de l'intervention -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_realisation">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_realisation" aria-expanded="false" aria-controls="accordion_collapse_realisation">
                                    <i class="bi bi-tools me-2"></i> 2 - Réalisation
                                </button>
                            </h2>
                            <div id="accordion_collapse_realisation" class="accordion-collapse collapse" aria-labelledby="accordion_header_realisation" data-bs-parent="#accordion_bi">
                                <div class="accordion-body">
                                    <div class="fullname-section">
                                        <div class="row align-items-end"> 
                                            <div class="col-md-6 mb-3">
                                                <div class="form-input d-flex align-items-center" style="height: calc(1.5em + 1.5rem + 2px);">
                                                    <input class="form-check-input mt-0 me-2" type="checkbox" name="intervention_realisable" value="oui" id="intervention_realisable">
                                                    <label class="form-check-label mb-0" for="intervention_realisable">Intervention réalisable</label>
                                                </div>   
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-group">
                                                    <label for="equipier" class="form-label">Equipier : </label>
                                                    <input type="text" class="form-input" id="equipier" name="equipier" value="{{ $data['equipier'] ?? '' }}" maxlength="30">
                                                </div>
                                            </div>
                                        </div>    
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Photo avant l'intervention :</label>
                                                <div class="mb-3">
                                                    <input type="file" id="photo_avant" name="photo_avant" accept="image/*" class="d-none">
                                                    <button type="button" id="btn_photo_avant" class="generate-btn w-100">
                                                        <i class="bi bi-camera"></i> Prendre une Photo
                                                    </button>
                                                </div>
                                                <div class="mb-3 text-center">
                                                    <img id="photo_avant_apercu" src="" alt="Aperçu" class="d-none rounded shadow-sm" style="width: 100%; height: 250px; object-fit: cover; border: 2px solid var(--neutral-200);">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Photo après l'intervention :</label>
                                                <div class="mb-3">
                                                    <input type="file" id="photo_apres" name="photo_apres" accept="image/*" capture="environment" class="d-none">
                                                    <button type="button" id="btn_photo_apres" class="generate-btn w-100">
                                                        <i class="bi bi-camera"></i> Prendre une Photo
                                                    </button>
                                                </div>
                                                <div class="mb-3 text-center">
                                                    <img id="photo_apres_apercu" src="" alt="Aperçu" class="d-none rounded shadow-sm" style="width: 100%; height: 250px; object-fit: cover; border: 2px solid var(--neutral-200);">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="compte_rendu" class="form-label">Compte-rendu d'intervention :</label>
                                                    <textarea class="form-input" id="compte_rendu" name="compte_rendu" rows="4" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                        </div>   
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="materiel" class="form-label">Matériel consommé ou à commander :</label>
                                                    <textarea class="form-input" id="materiel" name="materiel" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="prevoir" class="form-label">A prévoir et observation complémentaire :</label>
                                                    <textarea class="form-input" id="prevoir" name="prevoir" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="financial-summary p-3 mt-3">
                                            <div class="row"> 
                                                <div class="col-md-6 mb-2">                                                    
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="intervention_suite" value="oui" id="intervention_suite">
                                                        <label class="form-check-label" for="intervention_suite">Nouvelle intervention nécessaire</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-2">                                               
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="facturable" value="oui" id="facturable">
                                                        <label class="form-check-label" for="facturable">Facturable</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-2">                                        
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="terminee" value="oui" id="terminee">
                                                        <label class="form-check-label" for="terminee">Intervention terminée</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-2">                                 
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="devis_a_faire" value="oui" id="devis_a_faire">
                                                        <label class="form-check-label" for="devis_a_faire">Devis à réaliser</label>
                                                    </div> 
                                                </div>
                                                <div class="col-md-6 mb-2">                                        
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="absent" value="oui" id="absent">
                                                        <label class="form-check-label" for="absent">Client absent</label>
                                                    </div>   
                                                </div>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Troisième section : Complément de l'intervention -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_complement">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_complement" aria-expanded="false" aria-controls="accordion_collapse_complement">
                                    <i class="bi bi-images me-2"></i> 3 - Supplément Photos
                                </button>
                            </h2>
                            <div id="accordion_collapse_complement" class="accordion-collapse collapse" aria-labelledby="accordion_header_complement" data-bs-parent="#accordion_bi">
                                <div class="accordion-body">
                                    <div class="fullname-section text-center">
                                        <div id="complements_apercu" class="mt-3 row text-start"></div>

                                        <button type="button" class="generate-btn d-inline-flex mx-auto" onclick="document.getElementById('complement').click();">
                                            <i class="bi bi-plus-circle me-2"></i> Ajouter une photo
                                        </button>

                                        <!-- Input file caché qui déclenche l'appareil photo sur mobile -->
                                        <input type="file" id="complement" accept="image/*" multiple class="d-none">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Quatrième section : Complément de l'intervention -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_info_complementaire">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_info_complementaire" aria-expanded="false" aria-controls="accordion_collapse_info_complementaire">
                                    <i class="bi bi-plus-square me-2"></i> 4 - Informations complémentaires
                                </button>
                            </h2>
                            <div id="accordion_collapse_info_complementaire" class="accordion-collapse collapse" aria-labelledby="accordion_header_info_complementaire" data-bs-parent="#accordion_bi">
                                <div class="accordion-body">
                                    <div class="fullname-section">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="constat" class="form-label">Le constat : </label>
                                                    <textarea class="form-input" id="constat" name="constat" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="verification" class="form-label">Les vérifications :</label>
                                                    <select class="form-input mb-2" id="verification-select">
                                                        <option value="">Choisir une vérification...</option>
                                                        @if(isset($optionsVerification) && is_array($optionsVerification))
                                                            @foreach($optionsVerification as $option)
                                                                <option value="{{ $option }}">{{ $option }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <textarea class="form-input" id="verification" name="verification" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="notes_particulieres" class="form-label">Les notes particulières :</label>
                                                    <textarea class="form-input" id="notes_particulieres" name="notes_particulieres" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label for="points_vigilances" class="form-label">Les points de vigilances : </label>
                                                    <textarea class="form-input" id="points_vigilances" name="points_vigilances" rows="3" maxlength="500"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Gestion de la signature -->
                    <div class="signature-section mt-4">     
                        <h3 class="signature-title"><i class="bi bi-pencil-square"></i> Signature </h3>
                        <p class="text-muted small mb-3">Signature du client ou de son représentant :</p>
                        
                        <canvas id="signature-pad" class="signature-canvas w-100" style="height: 250px;"></canvas>
                        
                        <input type="hidden" name="signature" id="signature">
                        
                        <div class="signature-controls mt-3">
                            <button type="button" class="clear-button" id="clear-signature">
                                <i class="bi bi-eraser"></i> Effacer
                            </button>
                            <button type="button" class="sign-button" id="valide-signature">
                                <i class="bi bi-check-circle"></i> Valider la signature
                            </button>
                        </div>
                    </div>

                    <div class="document-details mt-4">
                        <div class="form-group">
                            <label class="form-label" for="fait-le">Fait le :</label>
                            <input min="{{ date("y-m-d") }}" type="date" class="form-input" name="fait-le" id="fait-le">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="view-pdf-button">
                            <i class="bi bi-file-earmark-pdf"></i> Valider l'intervention et générer le PDF
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="footer-branding">
                <p class="text-muted mb-0">Document généré numériquement</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let nombrePhoto = 0;

        document.getElementById("complement").addEventListener("change", function(event) {

            if (nombrePhoto === 40) {
                alert("Vous avez atteint le nombre maximum de photos (40). Veuillez supprimer des photos existantes avant d'en ajouter de nouvelles.");
                return;
            }

            const files = event.target.files;
            const previewContainer = document.getElementById("complements_apercu");
            const inputFile = document.getElementById("complement");

            // 🔍 Récupérer les fichiers stockés
            let storedFiles = JSON.parse(sessionStorage.getItem("storedFiles") || "[]");

            for (const file of files) {
                if (!file.type.startsWith("image/")) continue;

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        // Taille cible fixe pour les photos complémentaires (800x600)
                        const targetWidth = 800;
                        const targetHeight = 600;
                        const quality = 0.8;

                        const canvas = document.createElement("canvas");
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                        const ctx = canvas.getContext("2d");

                        // Fond blanc
                        ctx.fillStyle = "#FFFFFF";
                        ctx.fillRect(0, 0, canvas.width, canvas.height);

                        // Calcul du ratio (fit contain)
                        let scale = Math.min(targetWidth / img.width, targetHeight / img.height);
                        let x = (targetWidth / 2) - (img.width / 2) * scale;
                        let y = (targetHeight / 2) - (img.height / 2) * scale;

                        ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

                        canvas.toBlob(blob => {
                            const compressedFile = new File([blob], `compressed_${Date.now()}_${file.name}`, { type: "image/jpeg", lastModified: Date.now() });

                            // 🖥️ Envoyer l'image au serveur immédiatement
                            let formData = new FormData();
                            formData.append("client", "{{ $client }}");
                            formData.append("document", "{{ $document }}");
                            formData.append("uid", "{{ $uid }}");
                            formData.append("image", compressedFile);

                            fetch("/upload-visuel", {
                                method: "POST",
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json' // Force Laravel à répondre en JSON même s'il y a une erreur
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    let imageName = data.name; // Nom du fichier retourné par Laravel

                                    // ✅ Stocker uniquement le nom du fichier dans sessionStorage
                                    storedFiles.push(imageName);
                                    sessionStorage.setItem("storedFiles", JSON.stringify(storedFiles));

                                    // 🎨 Ajouter l'aperçu
                                    const div = document.createElement("div");
                                    div.classList.add("col-md-6", "mb-3");

                                    div.innerHTML = `
                                        <div class="card shadow-sm">
                                            <img src="${data.url}" class="card-img-top" alt="" style="height: 250px; object-fit: cover;">
                                            <div class="card-body">
                                                <textarea class="form-input mb-2" placeholder="Ajouter un commentaire..." name="comments[]" rows="3" maxlength="99"></textarea>
                                                <input type="hidden" name="images[]" value="${imageName}">
                                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="supprimerComplement(this, '${imageName}')">
                                                    <i class="bi bi-trash"></i> Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    `;

                                    previewContainer.appendChild(div);
                                    nombrePhoto += 1;
                                }
                            })
                            .catch(error => console.error("Erreur lors de l'upload :", error));
                        }, "image/jpeg", quality);
                    };
                };
            }
        });

        // 🗑️ Supprimer une image (local + serveur)
        function supprimerComplement(button, fileName) {
            const card = button.closest(".col-md-6");
            card.remove();

            // Supprimer du stockage local
            let storedFiles = JSON.parse(sessionStorage.getItem("storedFiles") || "[]");
            storedFiles = storedFiles.filter(name => name !== fileName);
            sessionStorage.setItem("storedFiles", JSON.stringify(storedFiles));

            // Facultatif : Supprimer aussi l'image du serveur via une requête AJAX
            fetch("/delete-visuel", {
                method: "POST",
                body: JSON.stringify({ client: "{{ $client }}", document: "{{ $document }}", uid: "{{ $uid }}", name: fileName }),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Content-Type": "application/json",
                }
            })
            .then(response => {
                response.json();

                if (response.ok) {
                    nombrePhoto -= 1;
                }
            })
            .catch(error => console.error("Erreur lors de la suppression :", error));
        }

    </script>


    <script>

        function imageConvertor(inputId, previewId) {
                const file = event.target.files[0];
                
                // Sécurité : stop si pas de fichier ou déjà traité
                if (!file || file._isProcessed) return;

                // Taille cible fixe pour l'envoi au serveur (800x600 px)
                const targetWidth = 800;
                const targetHeight = 600;
                const quality = 0.8;

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(e) {
                    let inputElement = document.getElementById(inputId);

                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = function() {
                        const canvas = document.createElement("canvas");
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                        const ctx = canvas.getContext("2d");

                        // Fond blanc pour remplir le canvas
                        ctx.fillStyle = "#FFFFFF";
                        ctx.fillRect(0, 0, canvas.width, canvas.height);

                        // Calcul du ratio pour centrer l'image tout en la redimensionnant (fit contain)
                        let scale = Math.min(targetWidth / img.width, targetHeight / img.height);
                        let x = (targetWidth / 2) - (img.width / 2) * scale;
                        let y = (targetHeight / 2) - (img.height / 2) * scale;

                        // On dessine l'image redimensionnée et centrée
                        ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

                        // Conversion en JPEG (le fichier réel qui sera envoyé)
                        canvas.toBlob(blob => {
                            const compressedFile = new File([blob], file.name, { 
                                type: "image/jpeg", 
                                lastModified: Date.now() 
                            });

                            // Marqueur pour éviter la boucle infinie sur l'event change
                            compressedFile._isProcessed = true;

                            // 1. Mise à jour de l'aperçu visuel
                            const imgPreview = document.getElementById(previewId);
                            if(imgPreview) {
                                imgPreview.src = URL.createObjectURL(compressedFile);
                                imgPreview.classList.remove("d-none");
                            }

                            // 2. Remplacement du fichier dans l'input pour l'envoi serveur
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(compressedFile);
                            inputElement.files = dataTransfer.files;

                        }, "image/jpeg", quality);
                    };
                };
        }


        function ouvrirNavigation() {
            // Adresse encodée pour l'URL
			const adresse = encodeURIComponent('{{ $data['adresse_intervention'] }}');

            // Vérification du type d'appareil
            if (navigator.userAgent.match(/iPhone|iPad|Mac/i)) {
                // Apple Plans sur iOS/macOS
                window.open("https://maps.apple.com/?q=" + adresse);
            } else {
                // Google Maps en mode navigation
                window.open("https://www.google.com/maps/dir/?api=1&destination=" + adresse);
            }
        }

        function modifierDateIntervention() {
            // Récupérer les éléments
            const dateDisplay = document.getElementById('date-intervention-display');
            const dateInput = document.getElementById('date-intervention-hidden');
            
            if (!dateDisplay || !dateInput) {
                console.error('Éléments de date non trouvés');
                return;
            }

            // Récupérer la date actuelle et la convertir au format YYYY-MM-DD
            const currentDateText = dateDisplay.textContent.trim();
            const [day, month, year] = currentDateText.split('/');
            const dateValue = `${year}-${month}-${day}`;

            // Créer et afficher la modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'dateModal';
            modal.setAttribute('tabindex', '-1');
            modal.setAttribute('aria-hidden', 'true');
            
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-calendar"></i> Modifier la date d'intervention
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="newDate" class="form-label">Nouvelle date :</label>
                                <input type="date" class="form-input" id="newDate" value="${dateValue}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="confirmDateBtn">Confirmer</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Afficher la modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Gérer le clic sur "Confirmer"
            modal.querySelector('#confirmDateBtn').addEventListener('click', function() {
                const newDate = modal.querySelector('#newDate').value;
                
                if (!newDate) {
                    alert('Veuillez sélectionner une date');
                    return;
                }

                // Convertir le format YYYY-MM-DD en DD/MM/YYYY
                const [newYear, newMonth, newDay] = newDate.split('-');
                const formattedDate = `${newDay}/${newMonth}/${newYear}`;

                // Mettre à jour l'affichage
                dateDisplay.textContent = formattedDate;
                
                // Mettre à jour l'input hidden
                dateInput.value = newDate;

                // Créer une notification de succès
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success d-flex align-items-center justify-content-center';
                successMsg.style.cssText = 'animation: slideDown 0.3s ease-out; position: fixed; top: 10%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; max-width: 400px;';
                successMsg.innerHTML = '<i class="bi bi-check-circle me-2"></i><strong>Date modifiée</strong> avec succès !';
                document.body.appendChild(successMsg);

                // Supprimer le message après 3 secondes
                setTimeout(() => {
                    successMsg.remove();
                }, 3000);

                // Fermer la modal
                bsModal.hide();

                // Supprimer la modal du DOM
                setTimeout(() => {
                    modal.remove();
                }, 300);
            });

            // Supprimer la modal du DOM quand elle est fermée
            modal.addEventListener('hidden.bs.modal', function() {
                modal.remove();
            });
        }

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Sélectionne tous les éléments qui déclenchent l'accordéon
            document.querySelectorAll(".accordion-button").forEach(button => {
                button.addEventListener("click", function() {
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth" // Effet de défilement fluide
                    });
                });
            });

                    // Fonction pour gérer le clic bouton -> input et l'aperçu
            function setupPhotoUpload(buttonId, inputId, previewId) {
                const btn = document.getElementById(buttonId);
                const input = document.getElementById(inputId);

                // 1. Quand on clique sur le bouton, on déclenche l'input masqué
                btn.addEventListener('click', () => input.click());

                // 2. Quand un fichier est choisi, on l'affiche
                input.addEventListener('change', function() {
                    imageConvertor(inputId, previewId);
                });
            }

            // Initialisation pour les deux champs
            setupPhotoUpload('btn_photo_avant', 'photo_avant', 'photo_avant_apercu');
            setupPhotoUpload('btn_photo_apres', 'photo_apres', 'photo_apres_apercu');

            sessionStorage.removeItem("storedFiles");

            // Gestion du select pour ajouter aux vérifications
            const verificationSelect = document.getElementById("verification-select");
            const verificationTextarea = document.getElementById("verification");

            if (verificationSelect && verificationTextarea) {
                verificationSelect.addEventListener("change", function() {
                    const selectedValue = this.value;
                    const maxLength = parseInt(verificationTextarea.getAttribute("maxlength")) || 500;
                    
                    if (selectedValue) {
                        // Ajouter le point virgule au texte sélectionné
                        const selectedWithSemicolon = selectedValue + " ; ";
                        
                        // Calculer la nouvelle longueur si on ajoute le texte
                        const currentValue = verificationTextarea.value;
                        const newLineChar = currentValue ? "\n" : "";
                        const potentialNewValue = currentValue + newLineChar + selectedWithSemicolon;
                        
                        // Vérifier si ça dépasse la limite
                        if (potentialNewValue.length > maxLength) {
                            // Afficher une infobulle d'erreur
                            const tooltip = document.createElement("div");
                            tooltip.className = "alert alert-warning d-inline-block position-absolute";
                            tooltip.style.cssText = "padding: 0.5rem 0.75rem; font-size: 0.875rem; z-index: 1000; animation: fadeInOut 2s ease-in-out;";
                            tooltip.textContent = "⚠️ Limite maximum de caractères atteinte";
                            tooltip.id = "char-limit-tooltip";
                            
                            // Supprimer l'ancienne infobulle s'il y en a une
                            const existingTooltip = document.getElementById("char-limit-tooltip");
                            if (existingTooltip) existingTooltip.remove();
                            
                            // Ajouter l'infobulle à côté du select
                            verificationSelect.parentNode.insertBefore(tooltip, verificationSelect.nextSibling);
                            
                            // Supprimer le tooltip après 2 secondes
                            setTimeout(() => {
                                tooltip.remove();
                            }, 2000);
                            
                            // Réinitialiser le select sans ajouter le texte
                            this.value = "";
                        } else {
                            // Ajouter le texte à la textarea avec point virgule et saut de ligne
                            if (currentValue) {
                                verificationTextarea.value += "\n" + selectedWithSemicolon;
                            } else {
                                verificationTextarea.value = selectedWithSemicolon;
                            }
                            
                            // Réinitialiser le select
                            this.value = "";
                        }
                    }
                });
            }

            // Ajouter l'animation CSS pour le fade in/out du tooltip
            const style = document.createElement("style");
            style.textContent = `
                @keyframes fadeInOut {
                    0% { opacity: 0; }
                    10% { opacity: 1; }
                    90% { opacity: 1; }
                    100% { opacity: 0; }
                }
                @keyframes slideDown {
                    0% { transform: translateY(-20px); opacity: 0; }
                    100% { transform: translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);

            let form = document.querySelector("form");
            let canvas = document.getElementById("signature-pad");
            let signatureInput = document.getElementById("signature");
            let clearButton = document.getElementById("clear-signature");
            let valideButton = document.getElementById("valide-signature");

            // Créer la signature avec gestion souris + tactile
            let signaturePad; // Variable pour stocker l'instance de SignaturePad

            function resizeCanvas() {
                let ratio = Math.max(window.devicePixelRatio || 1, 1);
                let displayWidth = canvas.clientWidth;
                let displayHeight = canvas.clientHeight;

                // Si le canvas n'a pas de dimensions CSS (par exemple, s'il est caché), ne pas tenter de le redimensionner.
                if (displayWidth === 0 || displayHeight === 0) {
                    console.warn("Canvas a des dimensions CSS de 0. Il est peut-être caché. ReTentative plus tard ou attendez qu'il soit visible.");
                    // Si c'est dans un modal, vous devrez appeler resizeCanvas() quand le modal est 'shown.bs.modal'
                    return;
                }

                // Sauvegarder les données de la signature si elle existe
                let existingSignatureData = null;
                if (signaturePad && !signaturePad.isEmpty()) {
                    existingSignatureData = signaturePad.toData();
                    
                }

                // 1. Définir la résolution interne du canvas en fonction du DPR
                canvas.width = displayWidth * ratio;
                canvas.height = displayHeight * ratio;

                // 2. Réinitialiser et appliquer le scaling au contexte de dessin
                let ctx = canvas.getContext("2d");
                ctx.setTransform(1, 0, 0, 1, 0, 0); // Réinitialise toute transformation précédente
                ctx.scale(ratio, ratio); // Applique le scaling pour que les dessins futurs respectent le DPR

                // 3. (Ré)initialiser Signature Pad avec les bonnes options
                if (signaturePad) {
                    // Si signaturePad existe déjà, il faut le détruire proprement et le recréer
                    signaturePad.off(); // Désinscrit les écouteurs d'événements
                    signaturePad = null;
                }
                signaturePad = new SignaturePad(canvas, {
                    minWidth: 0.5, // Épaisseur minimale du trait (en pixels CSS)
                    maxWidth: 2.5, // Épaisseur maximale du trait (en pixels CSS)
                    penColor: 'rgb(0, 0, 0)', // Couleur du trait
                    backgroundColor: 'rgba(255, 255, 255, 0)'
                });

                // 4. Recharger la signature si elle existait (pour conserver le dessin après redimensionnement)
                if (existingSignatureData) {
                    signaturePad.fromData(existingSignatureData);
                }

            }
            
            // Désactiver le resize après le premier chargement
            window.addEventListener("load", function () {
                resizeCanvas(); // Applique le redimensionnement une seule fois
                signaturePad.clear();
            });

            // Appeler la fonction de redimensionnement chaque fois que la fenêtre est redimensionnée
            window.addEventListener('resize', resizeCanvas);

            // Sauvegarder la signature au format Base64
            function saveSignature() {
                if (!signaturePad.isEmpty()) {
                    signatureInput.value = signaturePad.toDataURL();
                } else {
                    signatureInput.value = "";
                }
            }

            // Empêcher le resize intempestif en bloquant les événements sur mobile
            canvas.addEventListener("touchstart", function () {
                window.removeEventListener("resize", resizeCanvas);
            }, { passive: false });

            // Effacer la signature
            clearButton.addEventListener("click", function () {
                signaturePad.clear();
                signatureInput.value = "";

                signaturePad.on();
                valideButton.disabled = false;
                valideButton.style.background = ""; // Réinitialise au style du CSS
                valideButton.innerHTML = '<i class="bi bi-check-circle"></i> Valider la signature';
            });

            valideButton.addEventListener("click", function () {
                // Vérifier que la signature n'est pas vide
                if (signaturePad.isEmpty()) {
                    // Afficher une alerte
                    
                    const errorMsg = document.createElement("div");
                    errorMsg.className = "alert alert-danger d-flex align-items-center";
                    errorMsg.style.cssText = "animation: slideDown 0.3s ease-out;";
                    errorMsg.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i><strong>Une signature est requise</strong>&nbsppour valider la signature. Veuillez signer avant de continuer.';
                    errorMsg.id = "validation-signature-error";
                    
                    // Supprimer l'ancien message s'il existe
                    const existingError = document.getElementById("validation-signature-error");
                    if (existingError) existingError.remove();
                    
                    // Insérer le message au début du formulaire
                    form.insertBefore(errorMsg, form.firstChild);
                    
                    // Faire défiler jusqu'au message d'erreur
                    errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
                    
                    // Supprimer le message après 5 secondes
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 10000);

                    return; // Empêcher la validation
                }
                
                signaturePad.off();
                valideButton.disabled = true;
                valideButton.style.background = "linear-gradient(135deg, #64748b 0%, #334155 100%)"; // Gris ardoise
                valideButton.innerHTML = '<i class="bi bi-check-all"></i> Signature validée';
                valideButton.style.boxShadow = "none";
            });
            
            // S'assurer que la signature est bien enregistrée avant soumission

            /*
            
            form.addEventListener("submit", function (event) {
                
                saveSignature(); // Enregistre la signature avant l'envoi

                // Récupérer l'équipier et les éléments de la section Réalisation
                const equipierInput = document.getElementById("equipier");
                const realisationSection = document.getElementById("accordion_collapse_realisation");
                const realisationButton = document.querySelector('[data-bs-target="#accordion_collapse_realisation"]');

                // Vérifier que l'équipier est rempli
                if (!equipierInput.value || equipierInput.value.trim() === "") {
                    event.preventDefault(); // Empêcher la soumission
                    
                    // Ouvrir la section Réalisation si elle est fermée
                    if (realisationSection && !realisationSection.classList.contains("show")) {
                        realisationButton.click();
                        
                        // Attendre que la section s'ouvre avant de faire le focus
                        setTimeout(() => {
                            equipierInput.focus();
                        }, 400);
                    } else {
                        // Si la section est déjà ouverte, focus immédiat
                        equipierInput.focus();
                    }
                    
                    // Créer et afficher un message d'erreur
                    const errorMsg = document.createElement("div");
                    errorMsg.className = "alert alert-danger";
                    errorMsg.style.cssText = "animation: slideDown 0.3s ease-out; border-left: 5px solid #dc3545; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);";
                    errorMsg.innerHTML = `
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem; color: #dc3545;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-2" style="color: #dc3545;">
                                    <strong>Champ obligatoire manquant</strong>
                                </h5>
                                <p class="mb-0">
                                    Vous devez remplir le champ <strong>Equipier</strong> dans la section <strong>2 - Réalisation</strong> avant de pouvoir générer le PDF.
                                </p>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> La section s'est ouverte automatiquement pour vous.
                                </small>
                            </div>
                        </div>
                    `;
                    errorMsg.id = "equipier-error";
                    
                    // Supprimer l'ancien message s'il existe
                    const existingError = document.getElementById("equipier-error");
                    if (existingError) existingError.remove();
                    
                    // Insérer le message au début du formulaire
                    form.insertBefore(errorMsg, form.firstChild);
                    
                    // Faire défiler jusqu'au message d'erreur
                    setTimeout(() => {
                        errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
                    }, 100);
                    
                    // Supprimer le message après 5 secondes
                    setTimeout(() => {
                        if (errorMsg.parentNode) {
                            errorMsg.remove();
                        }
                    }, 10000);
                    
                    return; // Arrêter le traitement
                }

                */


                // Vérifier que la signature n'est pas vide

                /*

                if (!signatureInput.value || signaturePad.isEmpty()) {
                    event.preventDefault(); // Empêcher la soumission
                    
                    // Créer et afficher un message d'erreur en rouge
                    const errorMsg = document.createElement("div");
                    errorMsg.className = "alert alert-danger";
                    errorMsg.style.cssText = "animation: slideDown 0.3s ease-out; border-left: 5px solid #dc3545; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);";
                    errorMsg.innerHTML = `
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem; color: #dc3545;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-2" style="color: #dc3545;">
                                    <strong>Signature manquante</strong>
                                </h5>
                                <p class="mb-0">
                                    Une signature est requise pour générer le PDF. Veuillez signer dans la section Signature ci-dessous.
                                </p>
                            </div>
                        </div>
                    `;
                    errorMsg.id = "signature-error";
                    
                    // Supprimer l'ancien message s'il existe
                    const existingError = document.getElementById("signature-error");
                    if (existingError) existingError.remove();
                    
                    // Insérer le message au début du formulaire
                    form.insertBefore(errorMsg, form.firstChild);
                    
                    // Faire défiler jusqu'au message d'erreur
                    errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
                    
                    // Supprimer le message après 5 secondes
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 10000);
                } 
            });

            */

        });
    </script>
    
</body>
</html>