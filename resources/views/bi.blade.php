
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fiche d'intervention</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <!-- Favicon principal -->
    <link rel="icon" type="image/x-icon" href="../../favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="../../favicon-48x48.png">

    <!-- Icône pour Apple (iPhone/iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../apple-touch-icon.png">

    <!-- Icônes pour Android et PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="../../android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../../android-chrome-512x512.png">
    
</head>
<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Bi n°{{ $uid }}</div>
                    <div class="card-body">

                        <!-- Formulaire -->
                        <form action="{{ route('bi.submit', ['token' => $token]) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="alert alert-info" role="alert">
                                Travaux : <strong>{{ $data['description'] }}</strong>
                            </div>
                            
                            <div class="accordion" id="accordion_bi">
                                
                                <!-- Première section : information de l'intervention -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_information">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_information" aria-expanded="false" aria-controls="accordion_collapse_information">
                                            <h5>1 - Détail de l'intervention</h5>
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_information" class="accordion-collapse collapse" aria-labelledby="accordion_header_information" data-bs-parent="#accordion_bi">
                                        <div class="accordion-body">   
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="mb-3">
                                                        Adresse d'intervention : <br>
                                                        <strong>{{ $data['lieu_intervention'] }}<br>
                                                        {{ $data['adresse_intervention'] }}<br>
                                                        {{ $data['cp_intervention'] }} {{ $data['ville_intervention'] }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                    <!-- Bouton pour ouvrir l'adresse -->
                                                        <button type="button" class="btn btn-primary" style="margin-left: 12px;" onclick="ouvrirNavigation()">
                                                            📍 Itinéraire
                                                        </button>
                                                    </div>
                                                    <div class="mb-3">    
                                                        Intervenant : <strong>{{ $data['intervenant'] }}</strong><br>
                                                        Date d'intervention' :  <strong> {{ $data['date_intervention'] ?? date('d/m/Y') }} </strong>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        Adresse de facturation : <br>
                                                        <strong>{{ $data['nom_client'] }}<br>
                                                        {{ $data['adresse_facturation'] }}<br>
                                                        {{ $data['cp_facturation'] }} {{ $data['ville_facturation'] }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        Code client : <strong>{{ $data['code_client'] }}</strong><br>
                                                        Email : <strong>{{ $data['email_client'] }}</strong><br>
                                                        Téléphone : <strong>{{ $data['telephone_client'] }}</strong><br>
                                                        Portable : <strong>{{ $data['portable_client'] }}</strong><br>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Deuxième section : Réalisation de l'intervention -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_realisation">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_realisation" aria-expanded="false" aria-controls="accordion_collapse_realisation">
                                            <h5>2 - Réalisation</h5>
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_realisation" class="accordion-collapse collapse" aria-labelledby="accordion_header_realisation" data-bs-parent="#accordion_bi">
                                        <div class="accordion-body">
                                            <div class="row"> 
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="intervention_realisable" value="oui" id="intervention_realisable">
                                                        <label class="form-check-label" for="intervention_realisable">Intervention réalisable</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="equipier" class="form-label">Equipier :</label>
                                                    <input type="text" class="form-control" id="equipier" name="equipier" value="{{ $data['equipier'] ?? '' }}">
                                                </div>
                                            </div>    
                                            <div class="row"> 
                                                <div class="col-md-6 mb-3">
                                                    <label for="photo_avant" class="form-label">Photo avant l'intervention :</label>
                                                    <div class="mb-3">
                                                        <input type="file" id="photo_avant" name="photo_avant" accept="image/*" class="d-none">
                                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('photo_avant').click();">
                                                            📷 Télécharger ou prendre une Photo
                                                        </button>
                                                    </div>
                                                    <div class="mb-3">
                                                        <img id="photo_avant_apercu" src="" alt="Aperçu" class="img-fluid d-none" style="max-width: 350px; border: 1px solid #ddd; padding: 5px;">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="photo_apres" class="form-label">Photo après l'intervention :</label>
                                                    <div class="mb-3">
                                                        <input type="file" id="photo_apres" name="photo_apres" accept="image/*" capture="environment" class="d-none">
                                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('photo_apres').click();">
                                                            📷 Télécharger ou prendre une Photo
                                                        </button>
                                                    </div>
                                                    <div class="mb-3">
                                                        <img id="photo_apres_apercu" src="" alt="Aperçu" class="img-fluid d-none" style="max-width: 350px; border: 1px solid #ddd; padding: 5px;">
                                                    </div>
                                                </div>
                                            </div>   
                                            
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label for="compte_rendu" class="form-label">Compte-rendu d'intervention :</label>
                                                    <textarea class="form-control" id="compte_rendu" name="compte_rendu" rows="5"></textarea>
                                                </div>
                                            </div>   
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label for="materiel" class="form-label">Matériel consommé ou à commander :</label>
                                                    <textarea class="form-control" id="materiel" name="materiel" rows="5"></textarea>
                                                </div>
                                            </div>  

                                            <div class="row"> 
                                                <div class="col-md-6 mb-3">                                                    
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="intervention_suite" value="oui" id="intervention_suite">
                                                        <label class="form-check-label" for="intervention_suite">Nouvelle intervention nécessaire</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-3">                                               
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="facturable" value="oui" id="facturable">
                                                        <label class="form-check-label" for="facturable">Facturable</label>
                                                    </div>   
                                                </div>
                                            </div>   
                                            <div class="row"> 
                                                <div class="col-md-6 mb-3">                                        
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="terminee" value="oui" id="terminee">
                                                        <label class="form-check-label" for="terminee">Intervention terminée</label>
                                                    </div>   
                                                </div>
                                                <div class="col-md-6 mb-3">                                 
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="devis_a_faire" value="oui" id="devis_a_faire">
                                                        <label class="form-check-label" for="devis_a_faire">Devis à réaliser</label>
                                                    </div> 
                                                </div>
                                            </div>  

                                            <div class="row"> 
                                                <div class="col-md-6 mb-3">                                        
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="absent" value="oui" id="absent">
                                                        <label class="form-check-label" for="absent">Client absent</label>
                                                    </div>   
                                                </div>
                                            </div>  

                                        </div>
                                    </div>
                                </div>
                                <!-- Troisième section : Complément de l'intervention -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_complement">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_complement" aria-expanded="false" aria-controls="accordion_collapse_complement">
                                            <h5>3 - Complément</h5>
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_complement" class="accordion-collapse collapse" aria-labelledby="accordion_header_complement" data-bs-parent="#accordion_bi">
                                        <div class="accordion-body">

                                            <div id="complements_apercu" class="mt-3 row"></div>

                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('complement').click();">
                                                📸 Ajouter une photo
                                            </button>

                                            <!-- Input file caché qui déclenche l'appareil photo sur mobile -->
                                            <input type="file" id="complement" accept="image/*" multiple class="d-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Test si le client a une partie supplémentaire -->
                                @if (isset($client_layout))
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_customisation">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_customisation" aria-expanded="false" aria-controls="accordion_collapse_customisation">
                                            <h5>4 - Particularité</h5>
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_customisation" class="accordion-collapse collapse" aria-labelledby="accordion_header_complement" data-bs-parent="#accordion_bi">
                                        <div class="accordion-body">
                                            <!-- Layout modifier du client-->

                                            @include('custom/'. $client_layout['nom_layout'])

                                            <div id="complements_apercu" class="mt-3 row"></div>
                                            
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_signature">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_signature" aria-expanded="false" aria-controls="accordion_collapse_signature">
                                            <h5>5 - Signature</h5>
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_signature" class="accordion-collapse collapse" aria-labelledby="accordion_header_signature" data-bs-parent="#accordion_bi">
                                        <div class="accordion-body">
                                            
                                            <!-- Gestion de la signature -->
                                            <div class="col">     
                                                <label class="form-label" style="margin-left: 10%">Signature du client ou de son représentant :</label>
                                                <canvas id="signature-pad" class="border" style="width: 80%; height: 200px; margin-left: 10%; margin-right: 10%"></canvas>
                                                <input type="hidden" name="signature" id="signature">
                                                
                                                <button type="button" class="btn btn-secondary mt-2" id="clear-signature" style="margin-left: 10%">Effacer</button>
                                                <button type="button" class="btn btn-success mt-2" id="valide-signature">Valider</button>
    
                                                <hr>
                                            </div>
    
                                            <br>
                                            <div class="col-12">
                                                <label class="form-label visually-hidden" for="fait-le">Fait le :</label>
                                                <div class="input-group">
                                                <div class="input-group-text">Fait le :</div>
                                                <input min="{{ date("y-m-d") }}" type="date" class="form-control" name="fait-le" id="fait-le">
                                                </div>
                                            </div>
    
                                            <br>
    
                                        </div>
                                    </div>
                                </div>

                            </diV>
                            
                            <hr>
                            <!-- Validation du formulaire -->
                            <button type="submit" class="btn btn-success w-100">Valider l'intervention et générer le PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let nombrePhoto = 0;

        document.getElementById("complement").addEventListener("change", function(event) {

            if (nombrePhoto === 2) {
                return;
            }

            const files = event.target.files;
            const previewContainer = document.getElementById("complements_apercu");
            const inputFile = document.getElementById("complement");

            // 🔍 Récupérer les fichiers stockés
            let storedFiles = JSON.parse(sessionStorage.getItem("storedFiles") || "[]");

            console.log("Avant ajout, fichiers stockés:", storedFiles);

            for (const file of files) {
                if (!file.type.startsWith("image/")) continue;

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        const maxWidth = 800;
                        const maxHeight = 600;
                        const quality = 0.8;

                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxWidth) {
                                height *= maxWidth / width;
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width *= maxHeight / height;
                                height = maxHeight;
                            }
                        }

                        const canvas = document.createElement("canvas");
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0, width, height);

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
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Récupérer le token depuis la balise meta
                                },
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
                                        <div class="card">
                                            <img src="${data.url}" class="card-img-top" alt="" style="height: 400px; object-fit: contain;">
                                            <div class="card-body">
                                                <textarea class="form-control" placeholder="Ajouter un commentaire..." name="comments[]" rows="5"></textarea>
                                                <input type="hidden" name="images[]" value="${imageName}">
                                                <button type="button" class="btn btn-danger btn-sm mt-2" onclick="supprimerComplement(this, '${imageName}')">Supprimer</button>
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
                body: JSON.stringify({ client: "{{ $client }}", document: "{{ $document }}", uid: "{{ $uid}}", name: fileName }),
                headers: {
                    "Content-Type": "application/json",
                }
            })
            .then(response => {
                response.json();

                if (response.ok) {
                    nombrePhoto -= 1;
                }
            })
            .then(data => console.log(data))
            .catch(error => console.error("Erreur lors de la suppression :", error));
        }

    </script>


    <script>
        document.getElementById("photo_avant").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (!file) return;

            const maxWidth = 800; // Largeur max en pixels
            const maxHeight = 600; // Hauteur max
            const quality = 0.8; // Compression (1 = meilleure qualité, 0 = plus compressé)

            // Redimensionnement de l'image
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    const canvas = document.createElement("canvas");
                    const ctx = canvas.getContext("2d");

                    let width = img.width;
                    let height = img.height;

                    // Ajustement des dimensions
                    if (width > height) {
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width *= maxHeight / height;
                            height = maxHeight;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convertir en fichier JPEG
                    canvas.toBlob(blob => {
                        const compressedFile = new File([blob], file.name, { type: "image/jpeg", lastModified: Date.now() });

                        // Affichage de l'aperçu
                        const imgPreview = document.getElementById("photo_avant_apercu");
                        imgPreview.src = URL.createObjectURL(compressedFile);
                        imgPreview.classList.remove("d-none");

                        // Remplacement du fichier dans l'input pour le formulaire
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        document.getElementById("photo_avant").files = dataTransfer.files;
                    }, "image/jpeg", quality);
                };
            };
        });
        
        document.getElementById("photo_apres").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (!file) return;

            const maxWidth = 800; // Largeur max en pixels
            const maxHeight = 600; // Hauteur max
            const quality = 0.8; // Compression (1 = meilleure qualité, 0 = plus compressé)

            // Redimensionnement de l'image
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    const canvas = document.createElement("canvas");
                    const ctx = canvas.getContext("2d");

                    let width = img.width;
                    let height = img.height;

                    // Ajustement des dimensions
                    if (width > height) {
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width *= maxHeight / height;
                            height = maxHeight;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convertir en fichier JPEG
                    canvas.toBlob(blob => {
                        const compressedFile = new File([blob], file.name, { type: "image/jpeg", lastModified: Date.now() });

                        // Affichage de l'aperçu
                        const imgPreview = document.getElementById("photo_apres_apercu");
                        imgPreview.src = URL.createObjectURL(compressedFile);
                        imgPreview.classList.remove("d-none");

                        // Remplacement du fichier dans l'input pour le formulaire
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        document.getElementById("photo_apres").files = dataTransfer.files;
                    }, "image/jpeg", quality);
                };
            };
        });


        function ouvrirNavigation() {
            // Adresse encodée pour l'URL
            const adresse = encodeURIComponent('{{ $data['adresse_intervention'] }}, {{ $data['cp_intervention'] }} {{ $data['ville_intervention'] }}');

            // Vérification du type d'appareil
            if (navigator.userAgent.match(/iPhone|iPad|Mac/i)) {
                // Apple Plans sur iOS/macOS
                window.open("https://maps.apple.com/?q=" + adresse);
            } else {
                // Google Maps en mode navigation
                window.open("https://www.google.com/maps/dir/?api=1&destination=" + adresse);
            }
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

            sessionStorage.removeItem("storedFiles");

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

                console.log("Canvas redimensionné. CSS: " + displayWidth + "x" + displayHeight + "px. Interne: " + canvas.width + "x" + canvas.height + "px. DPR: " + ratio);
            }
            
            // Appeler la fonction de redimensionnement chaque fois que la fenêtre est redimensionnée
            window.addEventListener('resize', resizeCanvas);

            document.getElementById('accordion_collapse_signature').addEventListener('shown.bs.collapse', function () {
                resizeCanvas(); // Maintenant que l'élément est visible, le canvas peut être correctement redimensionné
            });

            // Sauvegarder la signature au format Base64
            function saveSignature() {
                if (!signaturePad.isEmpty()) {
                    signatureInput.value = signaturePad.toDataURL();
                    console.log("Signature enregistrée !");
                } else {
                    signatureInput.value = "";
                    console.log("Aucune signature détectée !");
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
            });

            valideButton.addEventListener("click", function () {
                signaturePad.off();
                valideButton.disabled = true;
            });
            
            // S'assurer que la signature est bien enregistrée avant soumission
            form.addEventListener("submit", function (event) {
                
                saveSignature(); // Enregistre la signature avant l'envoi

                
            });

            

        });
    </script>
    
</body>
</html>