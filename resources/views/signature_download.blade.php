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
                    <div class="card-header">Votre devis n°{{ $devis_id }} signé</div>
                    
                    <div class="card-body">
                        
                        <div class="mb-4">
          
                            <!-- Alert pour mettre en avant la signature du devis -->
                            <div class="alert alert-success text-center fw-bold d-flex align-items-center justify-content-center">
                                <span class="text-dark">✅ Devis signé avec succès ! <br> Vous pouvez maintenant le télécharger en cliquant sur le bouton en bas de cette page</span>
                            </div>
                            
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
                                    <i class="bi bi-file-earmark-pdf"></i> Télécharger le devis signé
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("viewPdfBtn").addEventListener("click", function() {
            let pdfUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
            let link = document.createElement("a");
            link.href = pdfUrl;
            link.download = "{{ $devis_id }}.pdf";  // 📌 Nom du fichier à télécharger
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>

</body>
</html>
