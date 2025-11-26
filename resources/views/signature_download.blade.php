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
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            padding: 3rem 0;
            animation: fadeInUp 0.8s ease-out;
        }

        .success-card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            background: white;
        }

        .success-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .card-header-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body-custom {
            padding: 2.5rem;
            background: #fafbfc;
        }

        .success-alert {
            background: var(--success-gradient);
            border: none;
            border-radius: 15px;
            color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        .success-alert .check-icon {
            font-size: 2rem;
            margin-right: 1rem;
            animation: bounceIn 1s ease-out;
        }

        .title-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .price-list {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .price-item {
            padding: 1.2rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-item:hover {
            background-color: #f8f9ff;
            transform: translateX(5px);
        }

        .price-item.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }

        .price-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: #f0f2ff;
            color: #667eea;
        }

        .download-btn {
            background: var(--success-gradient);
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 8px 25px rgba(78, 205, 196, 0.4);
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(78, 205, 196, 0.6);
        }

        .download-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .download-btn:hover::before {
            left: 100%;
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

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(102, 126, 234, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .floating-circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 80%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>

    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="success-card">
                    <div class="card-header-custom">
                        <h1 class="card-header-title">
                            <i class="bi bi-file-earmark-check-fill"></i>
                            Devis n°{{ $devis_id }} signé avec succès
                        </h1>
                    </div>
                    
                    <div class="card-body-custom">
                        <!-- Alert de succès avec animation -->
                        <div class="success-alert text-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle-fill check-icon"></i>
                                <div>
                                    <h4 class="mb-1">Signature réussie !</h4>
                                    <p class="mb-0">Votre devis est maintenant prêt au téléchargement</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Titre du devis -->
                        <div class="title-badge text-center">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            <strong>{{ $titre }}</strong>
                        </div>

                        <!-- Liste des prix modernisée -->
                        <div class="price-list">
                            <div class="price-item">
                                <div class="d-flex align-items-center">
                                    <div class="price-icon">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <span>Montant HT</span>
                                </div>
                                <strong>{{ number_format($montant_HT, 2, ',', ' ') }} €</strong>
                            </div>
                            <div class="price-item">
                                <div class="d-flex align-items-center">
                                    <div class="price-icon">
                                        <i class="bi bi-percent"></i>
                                    </div>
                                    <span>Montant TVA</span>
                                </div>
                                <strong>{{ number_format($montant_TVA, 2, ',', ' ') }} €</strong>
                            </div>
                            <div class="price-item total">
                                <div class="d-flex align-items-center">
                                    <div class="price-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="bi bi-cash-coin"></i>
                                    </div>
                                    <span><strong>Total TTC</strong></span>
                                </div>
                                <strong>{{ number_format($montant_TTC, 2, ',', ' ') }} €</strong>
                            </div>
                        </div>

                        <!-- Bouton de téléchargement modernisé -->
                        <div class="text-center">
                            <button id="viewPdfBtn" class="download-btn">
                                <i class="bi bi-cloud-download me-2"></i>
                                Télécharger le devis signé
                            </button>
                            <p class="text-muted mt-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Le fichier sera téléchargé au format PDF
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("viewPdfBtn").addEventListener("click", function() {
            // Animation du bouton lors du clic
            this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Préparation du téléchargement...';
            this.disabled = true;
            
            setTimeout(() => {
                let pdfUrl = "{{ url('download-devis/' . $organisation_id . '/' .$devis_id.'_'.$token ) }}";
                let link = document.createElement("a");
                link.href = pdfUrl;
                link.download = "{{ $devis_id }}.pdf";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Restaurer le bouton
                this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Téléchargement effectué !';
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-cloud-download me-2"></i>Télécharger le devis signé';
                    this.disabled = false;
                }, 2000);
            }, 1000);
        });

        // Animation d'apparition progressive des éléments
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.price-item');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s both`;
                }, 300);
            });
        });
    </script>
</body>
</html>
