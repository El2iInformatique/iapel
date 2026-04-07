<?php
    $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf');
?>

@if(file_exists($pdfPath))
    <script>
        window.location.href = "{{ route('pdf.view', ['client' => $client, 'document' => $document,'uid' => $uid]) }}";
    </script>
@else
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Formulaire CERFA 15497</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <style>
        .required-asterisk {
            color: red;
            margin-left: 0.25rem;
        }
        input:disabled, input[disabled], textarea:disabled, textarea[disabled] {
            background-color: #d3d3d3 !important;
            color: #666666 !important;
            cursor: not-allowed !important;
            opacity: 1 !important;
            border: 1px solid #999999 !important;
        }
        @keyframes slideDown {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="signature-container">
            <div class="header-section">
                <div class="header-content">
                    <div class="status-badge">
                        <i class="bi bi-file-earmark-text"></i> Formulaire CERFA
                    </div>
                    <h1 class="header-title">Formulaire CERFA 15497</h1>
                    <p class="header-subtitle">N° {{ $uid }}</p>
                </div>
            </div>

            <div class="content-section">

                <form action="{{ route('bi.submit', ['token' => $token]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="accordion" id="accordion_cerfa">

                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_intervenants">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_intervenants" aria-expanded="false" aria-controls="accordion_collapse_intervenants">
                                    <i class="bi bi-person-vcard me-2"></i> 1 - Intervenants (Opérateur et Détenteur)
                                </button>
                            </h2>
                            <div id="accordion_collapse_intervenants" class="accordion-collapse collapse" aria-labelledby="accordion_header_intervenants" data-bs-parent="#accordion_cerfa">
                                <div class="accordion-body">
                                    <h5 class="mt-2">[1] Opérateur</h5>
                                    <hr>
                                    <div class="mb-3">
                                        <label for="operateur_nom" class="form-label">Nom :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="operateur_nom" value="{{ old('operateur_nom', $cerfaConfig['nom'] ?? '') }}" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="operateur_adresse" class="form-label">Adresse :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="operateur_adresse" value="{{ old('operateur_adresse', $cerfaConfig['adresse'] ?? '') }}" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="operateur_siret" class="form-label">Numéro SIRET :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="operateur_siret" value="{{ old('operateur_siret', $cerfaConfig['siret'] ?? '') }}" maxlength="14" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="operateur_numero_attestation_capacite" class="form-label">Numéro d'attestation de capacité :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="operateur_numero_attestation_capacite" value="{{ old('operateur_numero_attestation_capacite', $cerfaConfig['numeroAttestationCapacite'] ?? '') }}" maxlength="50" disabled>
                                    </div>
                                    
                                    <h5 class="mt-4">[2] Détenteur</h5>
                                    <hr>
                                    <div class="mb-3">
                                        <label for="detenteur_nom" class="form-label">Nom :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="detenteur_nom" name="detenteur_nom" value="{{ old('detenteur_nom', $data['detenteur'] ?? '') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="detenteur_adresse" class="form-label">Adresse :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="detenteur_adresse" name="detenteur_adresse" value="{{ old('detenteur_adresse', $data['detenteur_adresse'] ?? '') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="detenteur_siret" class="form-label">Numéro SIRET :<span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-input" id="detenteur_siret" name="detenteur_siret" value="{{ old('detenteur_siret', $data['detenteur_siret'] ?? '') }}" maxlength="14" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_equipement">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_equipement" aria-expanded="false" aria-controls="accordion_collapse_equipement">
                                    <i class="bi bi-tools me-2"></i> 2 - Équipement et Nature de l'intervention
                                </button>
                            </h2>
                            <div id="accordion_collapse_equipement" class="accordion-collapse collapse" aria-labelledby="accordion_header_equipement" data-bs-parent="#accordion_cerfa">
                                <div class="accordion-body">
                                    <h5 class="mt-2">[3] Equipement concerné</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="identification" class="form-label">Identification :</label>
                                            <input type="text" class="form-input" id="identification" name="identification" value="{{ old('identification') }}" maxlength="48">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="denomination" class="form-label">Dénomination du fluide :</label>
                                            <input type="text" class="form-input" id="denomination" name="denomination" value="{{ old('denomination') }}" maxlength="7">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="charge" class="form-label">Charge totale (en kg) :</label>
                                            <input type="number" class="form-input" id="charge" name="charge" value="{{ old('charge') }}" maxlength="7" min="0" step="0.01">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tonnage" class="form-label">Tonnage équivalent CO² (t. éq CO2) :</label>
                                            <input type="number" class="form-input" id="tonnage" name="tonnage" value="{{ old('tonnage') }}" maxlength="5" min="0" step="0.01">
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[4] Nature de l'intervention</h5>
                                    <hr>
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="assemblage" id="assemblage">
                                                    <label class="form-check-label" for="assemblage">Assemblage de l'équipement</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="mise_service" id="mise_service">
                                                    <label class="form-check-label" for="mise_service">Mise en service de l'équipement</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="modification" id="modification">
                                                    <label class="form-check-label" for="modification">Modification de l'équipement</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="maintenance" id="maintenance">
                                                    <label class="form-check-label" for="maintenance">Maintenance de l'équipement</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="controle_periodique" id="controle_periodique">
                                                    <label class="form-check-label" for="controle_periodique">Contrôle d'étanchéité périodique</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="controle_non_periodique" id="controle_non_periodique">
                                                    <label class="form-check-label" for="controle_non_periodique">Contrôle d'étanchéité non périodique</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="dementelement" id="dementelement">
                                                    <label class="form-check-label" for="dementelement">Démentèlement</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nature_intervention" value="autre" id="autre">
                                                    <label class="form-check-label" for="autre">Autre (préciser)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-input" id="autre_valeur" name="autre_valeur" value="{{ old('autre_valeur') }}" maxlength="14" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_controle">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_controle" aria-expanded="false" aria-controls="accordion_collapse_controle">
                                    <i class="bi bi-search me-2"></i> 3 - Contrôles et Détection des fuites
                                </button>
                            </h2>
                            <div id="accordion_collapse_controle" class="accordion-collapse collapse" aria-labelledby="accordion_header_controle" data-bs-parent="#accordion_cerfa">
                                <div class="accordion-body">
                                    <h5 class="mt-2">[5] Détecteur manuel de fuite</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="identification_controle" class="form-label">Identification :</label>
                                            <input type="text" name="identification_controle" class="form-input" id="identification_controle" value="{{ old('identification_controle', $cerfaConfig['identificationControle'] ?? '') }}" maxlength="20">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="date_controle" class="form-label">Contrôlé le :</label>
                                            <input type="date" class="form-input" id="date_controle" value="{{ old('date_controle', $cerfaConfig['controleMaterielDate'] ?? date('Y-m-d')) }}" disabled>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[6] Présence d'un système permanent de détection de fuites</h5>
                                    <hr>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="detection_fuites" value="oui" id="detection_oui"
                                                {{ isset($data['detection_fuites']) && $data['detection_fuites'] === 'oui' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="detection_oui">Oui</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="detection_fuites" value="non" id="detection_non"
                                                {{ !isset($data['detection_fuites']) || $data['detection_fuites'] === 'non' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="detection_non">Non</label>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[7] Quantité de fluide frigorigène dans l'équipement</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">HCFC : </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hcfc" value="2-30" id="2-30">
                                                <label class="form-check-label" for="2-30">2 kg < Q < 30 kg</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hcfc" value="30-300" id="30-300">
                                                <label class="form-check-label" for="30-300">30 kg < Q < 300 kg</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hcfc" value="300" id="300">
                                                <label class="form-check-label" for="300">Q > 300 kg</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">HFC / PFC : </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfc_pfc" value="5-50" id="5-50">
                                                <label class="form-check-label" for="5-50">5 t < teqCO2 < 50 t</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfc_pfc" value="50-500" id="50-500">
                                                <label class="form-check-label" for="50-500">50 t < teqCO2 < 500 t</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfc_pfc" value="500" id="500">
                                                <label class="form-check-label" for="500">teqCO2 > 500 t</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <label class="form-label">HFO : </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfo" value="1-10" id="1-10">
                                                <label class="form-check-label" for="1-10">1kg ≤ Q < 10 kg</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfo" value="10-100" id="10-100">
                                                <label class="form-check-label" for="10-100">10 kg ≤ Q < 100 kg</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="hfo" value="100" id="100">
                                                <label class="form-check-label" for="100">Q ≥ 100 kg</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="mt-4">[8] Équip. HCFC, HFC et HFO sans système permanent de détection des fuites</h5>
                                            <hr>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_sans_detection" value="sans12" id="sans12">
                                                <label class="form-check-label" for="sans12">12 mois</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_sans_detection" value="sans6" id="sans6">
                                                <label class="form-check-label" for="sans6">6 mois</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_sans_detection" value="sans3" id="sans3">
                                                <label class="form-check-label" for="sans3">3 mois</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mt-4">[9] Équipements HFC et HFO avec système permanent de détection des fuites</h5>
                                            <hr>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_avec_detection" value="avec24" id="avec24">
                                                <label class="form-check-label" for="avec24">24 mois</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_avec_detection" value="avec12" id="avec12">
                                                <label class="form-check-label" for="avec12">12 mois</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="equipement_avec_detection" value="avec6" id="avec6">
                                                <label class="form-check-label" for="avec6">6 mois</label>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[10] Fuites constatées lors du contrôle d'étanchéité</h5>
                                    <hr>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="constat_fuites" value="oui" id="constat_oui"
                                                {{ isset($data['constat_fuites']) && $data['constat_fuites'] === 'oui' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="constat_oui">Oui</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="constat_fuites" value="non" id="constat_non"
                                                {{ !isset($data['constat_fuites']) || $data['constat_fuites'] === 'non' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="constat_non">Non</label>
                                        </div>
                                    </div>

                                    <div id="localisation_fuites" style="display: block">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="localisation_fuite_1">Localisation fuite n°1 :</label>
                                            <textarea class="form-input" id="localisation_fuite_1" name="localisation_fuite_1" rows="2" maxlength="72"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Réparation de la fuite n°1 :</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_1" value="reparation_fuite_1_fait" id="reparation_fuite_1_fait">
                                                <label class="form-check-label" for="reparation_fuite_1_fait">Réalisée</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_1" value="reparation_fuite_1_A_Faire" id="reparation_fuite_1_a_faire">
                                                <label class="form-check-label" for="reparation_fuite_1_a_faire">A faire</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="localisation_fuite_2">Localisation fuite n°2 :</label>
                                            <textarea class="form-input" id="localisation_fuite_2" name="localisation_fuite_2" rows="2" maxlength="72"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Réparation de la fuite n°2 :</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_2" value="reparation_fuite_2_fait" id="reparation_fuite_2_fait">
                                                <label class="form-check-label" for="reparation_fuite_2_fait">Réalisée</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_2" value="reparation_fuite_2_A_Faire" id="reparation_fuite_2_a_faire">
                                                <label class="form-check-label" for="reparation_fuite_2_a_faire">A faire</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="localisation_fuite_3">Localisation fuite n°3 :</label>
                                            <textarea class="form-input" id="localisation_fuite_3" name="localisation_fuite_3" rows="2" maxlength="72"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Réparation de la fuite n°3 :</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_3" value="reparation_fuite_3_fait" id="reparation_fuite_3_fait">
                                                <label class="form-check-label" for="reparation_fuite_3_fait">Réalisée</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="reparation_fuite_3" value="reparation_fuite_3_A_Faire" id="reparation_fuite_3_a_faire">
                                                <label class="form-check-label" for="reparation_fuite_3_a_faire">A faire</label>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_fluides">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_fluides" aria-expanded="false" aria-controls="accordion_collapse_fluides">
                                    <i class="bi bi-droplet me-2"></i> 4 - Manipulation et Destination des fluides
                                </button>
                            </h2>
                            <div id="accordion_collapse_fluides" class="accordion-collapse collapse" aria-labelledby="accordion_header_fluides" data-bs-parent="#accordion_cerfa">
                                <div class="accordion-body">
                                    <h5 class="mt-2">[11] Manipulation du fluide frigorigène</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantite_chargee_totale" class="form-label" >Quantité chargée totale (A+B+C) (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_chargee_totale" name="quantite_chargee_totale" value="{{ old('quantite_chargee_totale') }}" maxlength="8" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantite_chargee_A" class="form-label">A - Dont fluide vierge (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_chargee_A" name="quantite_chargee_A" value="{{ old('quantite_chargee_A') }}" maxlength="8" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fluide_A" class="form-label">Dénomination du fluide chargé si changement :</label>
                                                <input type="text" class="form-input" id="fluide_A" name="fluide_A" value="{{ old('fluide_A') }}" maxlength="8">
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantite_chargee_B" class="form-label">B - Dont fluide recyclé (fluide récupéré et réintrooduit) (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_chargee_B" name="quantite_chargee_B" value="{{ old('quantite_chargee_B') }}" maxlength="7" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantite_chargee_C" class="form-label">C - Dont fluide régénéré (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_chargee_C" name="quantite_chargee_C" value="{{ old('quantite_chargee_C') }}" maxlength="7" min="0" step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantite_recuperee_totale" class="form-label">Quantité de fluide récupérée totale (D+E) (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_recuperee_totale" name="quantite_recuperee_totale" value="{{ old('quantite_recuperee_totale') }}" maxlength="7" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantite_recuperee_D" class="form-label">D - Dont fluide destiné au traitement (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_recuperee_D" name="quantite_recuperee_D" value="{{ old('quantite_recuperee_D') }}" maxlength="7" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="BSFF" class="form-label">Si connu, numéro du BSFF (Trackdéchets) :</label>
                                                <input type="number" class="form-input" id="BSFF" name="BSFF" value="{{ old('BSFF') }}" maxlength="7" min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantite_recuperee_E" class="form-label">E - Dont fluide conservé pour réutilisation (réintroduction) (kg) :</label>
                                                <input type="number" class="form-input" id="quantite_recuperee_E" name="quantite_recuperee_E" value="{{ old('quantite_recuperee_E') }}" maxlength="7" min="0" step="0.01">
                                            </div>
                                            <div class="mb-3">
                                                <label for="identification_E" class="form-label">Identification du ou des contenants :</label>
                                                <input type="text" class="form-input" id="identification_E" name="identification_E" value="{{ old('identification_E') }}" maxlength="7">
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[12] Dénomination ADR/RID</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 d-flex flex-column">
                                            <h6 class="mt-3" style="min-height: 3.5rem;">Rubrique Déchets : 14 06 01* – CFC, HCFC, HFC, mélange HFC/HFO – Fluides non-inflammables</h6>
                                            <div class="form-check mb-2" style="min-height: 3.5rem;">
                                                <input class="form-check-input" type="radio" name="fluide_non_inflammable" value="UN1078" id="UN1078">
                                                <label class="form-check-label" for="UN1078">UN 1078, Déchet Gaz frigorifique NSA (Gaz réfrigérant, NSA), 2.2 (C/E)</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="fluide_non_inflammable" value="autre_cas_non_inflammable" id="autre_cas_non_inflammable">
                                                <label class="form-check-label" for="autre_cas_non_inflammable">Autre cas de fluides frigorigènes non-inflammables :</label>
                                            </div>
                                            <div class="mt-auto mb-3">
                                                <input type="text" class="form-input" id="autre_fluide_non_inflammable" name="autre_fluide_non_inflammable" value="{{ old('autre_fluide_non_inflammable') }}" maxlength="15" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex flex-column">
                                            <h6 class="mt-3" style="min-height: 3.5rem;">Rubrique Déchets : 16 05 04* – HFC-mélange HFC/HFO – Fluides inflammables</h6>
                                            <div class="form-check mb-2" style="min-height: 3.5rem;">
                                                <input class="form-check-input" type="radio" name="fluide_inflammable" value="UN3161" id="UN3161">
                                                <label class="form-check-label" for="UN3161">UN 3161, Déchet Gaz liquéfié inflammable NSA, 2.1 (B/D)</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="fluide_inflammable" value="autre_cas_inflammable" id="autre_cas_inflammable">
                                                <label class="form-check-label" for="autre_cas_inflammable">Autre cas de fluides frigorigènes inflammables :</label>
                                            </div>
                                            <div class="mt-auto mb-3">
                                                <input type="text" class="form-input" id="autre_fluide_inflammable" name="autre_fluide_inflammable" value="{{ old('autre_fluide_non_inflammable') }}" maxlength="18" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">[13] Installation prévue de destination </h5>
                                    <hr>
                                    <div class="mb-3">
                                        <textarea class="form-input" id="installation_destination_fluide" name="installation_destination_fluide" rows="2" maxlength="132"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="accordion_header_observations">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_observations" aria-expanded="false" aria-controls="accordion_collapse_observations">
                                    <i class="bi bi-journal-text me-2"></i> 5 - Observations
                                </button>
                            </h2>
                            <div id="accordion_collapse_observations" class="accordion-collapse collapse" aria-labelledby="accordion_header_observations" data-bs-parent="#accordion_cerfa">
                                <div class="accordion-body">
                                    <h5 class="mt-2">[14] Observations : </h5>
                                    <hr>
                                    <div class="mb-3">
                                        <textarea class="form-input" id="observations" name="observations" rows="5" maxlength="330"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="signature-section mt-4">     
                        <h3 class="signature-title"><i class="bi bi-pencil-square"></i> Signatures </h3>
                        
                        <div class="row mt-3">
                            <div class="col-md-6 mb-4">
                                <h6 class="mt-3 text-muted">Opérateur</h6>
                                <div class="mb-3">
                                    <label for="nom_signataire_operateur" class="form-label">Nom du signataire :</label>
                                    <input type="text" class="form-input" id="nom_signataire_operateur" name="nom_signataire_operateur" value="{{ old('nom_signataire_operateur') }}" maxlength="27">
                                </div>
                                <div class="mb-3">
                                    <label for="qualite_signataire_operateur" class="form-label">Qualité du signataire :</label>
                                    <input type="text" class="form-input" id="qualite_signataire_operateur" name="qualite_signataire_operateur" value="{{ old('qualite_signataire_operateur', $cerfaConfig['OperateurSignataireQualiter'] ?? '') }}" maxlength="27">
                                </div>
                                
                                <p class="text-muted small mb-2">Signature de l'opérateur :</p>
                                <canvas id="signature-pad-operateur" class="signature-canvas w-100 border" style="height: 200px;"></canvas>
                                <input type="hidden" name="signature-operateur" id="signature-operateur">
                                
                                <div class="signature-controls mt-3">
                                    <button type="button" class="clear-button" id="clear-signature-operateur">
                                        <i class="bi bi-eraser"></i> Effacer
                                    </button>
                                    <button type="button" class="sign-button" id="valide-signature-operateur">
                                        <i class="bi bi-check-circle"></i> Valider la signature
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6 class="mt-3 text-muted">Détenteur</h6>
                                <div class="mb-3">
                                    <label for="nom_signataire_detenteur" class="form-label">Nom du signataire :</label>
                                    <input type="text" class="form-input" id="nom_signataire_detenteur" name="nom_signataire_detenteur" value="{{ old('nom_signataire_detenteur') }}" maxlength="26">
                                </div>
                                <div class="mb-3">
                                    <label for="qualite_signataire_detenteur" class="form-label">Qualité du signataire :</label>
                                    <input type="text" class="form-input" id="qualite_signataire_detenteur" name="qualite_signataire_detenteur" value="{{ old('qualite_signataire_detenteur') }}" maxlength="26">
                                </div>

                                <p class="text-muted small mb-2">Signature du détenteur :</p>
                                <canvas id="signature-pad-detenteur" class="signature-canvas w-100 border" style="height: 200px;"></canvas>
                                <input type="hidden" name="signature-detenteur" id="signature-detenteur">
                                
                                <div class="signature-controls mt-3">
                                    <button type="button" class="clear-button" id="clear-signature-detenteur">
                                        <i class="bi bi-eraser"></i> Effacer
                                    </button>
                                    <button type="button" class="sign-button" id="valide-signature-detenteur">
                                        <i class="bi bi-check-circle"></i> Valider la signature
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="view-pdf-button" style="margin-bottom: -5px;">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // Comportement de retour en haut des accordéons
        document.querySelectorAll(".accordion-button").forEach(button => {
            button.addEventListener("click", function() {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            });
        });

        let autreCheckbox = document.getElementById("autre");
        let autreValeur = document.getElementById("autre_valeur");

        let localisationFuitesRadioOui = document.getElementById("constat_oui");
        let localisationFuitesRadioNon = document.getElementById("constat_non");
        let localisationFuitesValeur = document.getElementById("localisation_fuites");

        let canvasDetenteur = document.getElementById("signature-pad-detenteur");
        let signatureInputDetenteur = document.getElementById("signature-detenteur");
        let clearButtonDetenteur = document.getElementById("clear-signature-detenteur");
        let valideButtonDetenteur = document.getElementById("valide-signature-detenteur");
        
        let canvasOperateur = document.getElementById("signature-pad-operateur");
        let signatureInputOperateur = document.getElementById("signature-operateur");
        let clearButtonOperateur = document.getElementById("clear-signature-operateur");
        let valideButtonOperateur = document.getElementById("valide-signature-operateur");
        
        let form = document.querySelector("form");

        // Fonction pour activer/désactiver le champ "Autre"
        function toggleAutreField() {
            if (autreCheckbox.checked) {
                autreValeur.disabled = false;
            } else {
                autreValeur.disabled = true;
                autreValeur.value = ""; // Vider le champ quand on décoche
            }
        }

        function toggleLocalisationFuitesField() {
            if (localisationFuitesRadioOui.checked) {
                localisationFuitesValeur.style.display = "block";
            } else {
                localisationFuitesValeur.style.display = "none";
            }
        }

        // Vérifier si la case est cochée au chargement de la page
        toggleAutreField();
        toggleLocalisationFuitesField();

        // Écouter le changement de la case à cocher
        autreCheckbox.addEventListener("change", toggleAutreField);
        localisationFuitesRadioOui.addEventListener("change", toggleLocalisationFuitesField);
        localisationFuitesRadioNon.addEventListener("change", toggleLocalisationFuitesField);

        // Écouter tous les radio buttons nature_intervention pour mettre à jour l'état du champ "Autre"
        const natureRadios = document.querySelectorAll('input[name="nature_intervention"]');
        natureRadios.forEach(radio => {
            radio.addEventListener('change', toggleAutreField);
        });

        // Gestion des champs "Autres cas de fluides"
        let autreFluidNonInflammableRadio = document.getElementById("autre_cas_non_inflammable");
        let autreFluidNonInflammableField = document.getElementById("autre_fluide_non_inflammable");
        let autreFluidInflammableRadio = document.getElementById("autre_cas_inflammable");
        let autreFluidInflammableField = document.getElementById("autre_fluide_inflammable");

        // Fonction pour activer/désactiver le champ "Autre fluide non-inflammable"
        function toggleAutreFluidNonInflammable() {
            if (autreFluidNonInflammableRadio.checked) {
                autreFluidNonInflammableField.disabled = false;
            } else {
                autreFluidNonInflammableField.disabled = true;
                autreFluidNonInflammableField.value = "";
            }
        }

        // Fonction pour activer/désactiver le champ "Autre fluide inflammable"
        function toggleAutreFluidInflammable() {
            if (autreFluidInflammableRadio.checked) {
                autreFluidInflammableField.disabled = false;
            } else {
                autreFluidInflammableField.disabled = true;
                autreFluidInflammableField.value = "";
            }
        }

        // Vérifier l'état au chargement
        toggleAutreFluidNonInflammable();
        toggleAutreFluidInflammable();

        // Écouter tous les radio buttons fluide_non_inflammable
        const fluidNonInflammableRadios = document.querySelectorAll('input[name="fluide_non_inflammable"]');
        fluidNonInflammableRadios.forEach(radio => {
            radio.addEventListener('change', toggleAutreFluidNonInflammable);
        });

        // Écouter tous les radio buttons fluide_inflammable
        const fluidInflammableRadios = document.querySelectorAll('input[name="fluide_inflammable"]');
        fluidInflammableRadios.forEach(radio => {
            radio.addEventListener('change', toggleAutreFluidInflammable);
        });

        // Forcer les champs numériques à accepter uniquement des nombres
        const numericFields = [
            'charge', 'tonnage', 'quantite_chargee_totale', 'quantite_chargee_A',
            'quantite_chargee_B', 'quantite_chargee_C', 'quantite_recuperee_totale',
            'quantite_recuperee_D', 'BSFF', 'quantite_recuperee_E', 'numero_attestation_capacite'
        ];

        numericFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Empêcher les caractères non-numériques avant qu'ils ne soient saisis
                field.addEventListener('keypress', function(e) {
                    const char = String.fromCharCode(e.which);
                    // Accepter seulement les chiffres et le point (pour décimales)
                    if (!/[0-9.]/.test(char)) {
                        e.preventDefault();
                    }
                    // Vérifier la limite de caractères
                    const maxLength = parseInt(field.getAttribute('maxlength')) || 10;
                    if (field.value.length >= maxLength) {
                        e.preventDefault();
                    }
                });

                // Nettoyer au paste
                field.addEventListener('paste', function(e) {
                    e.preventDefault();
                    let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    // Garder seulement les chiffres et les points
                    pastedText = pastedText.replace(/[^0-9.]/g, '');
                    // Empêcher plusieurs points
                    if ((pastedText.match(/\./g) || []).length > 1) {
                        pastedText = pastedText.replace(/\.(?=.*\.)/g, '');
                    }
                    // Respecter la limite de caractères
                    const maxLength = parseInt(field.getAttribute('maxlength')) || 10;
                    if (pastedText.length > maxLength) {
                        pastedText = pastedText.substring(0, maxLength);
                    }
                    field.value = pastedText;
                });

                // Nettoyer l'input en temps réel (au cas où)
                field.addEventListener('input', function(e) {
                    let value = e.target.value;
                    value = value.replace(/[^0-9.]/g, '');
                    if ((value.match(/\./g) || []).length > 1) {
                        value = value.replace(/\.(?=.*\.)/g, '');
                    }
                    // Respecter la limite de caractères
                    const maxLength = parseInt(field.getAttribute('maxlength')) || 10;
                    if (value.length > maxLength) {
                        value = value.substring(0, maxLength);
                    }
                    e.target.value = value;
                });
            }
        });

        // Créer la signature avec gestion souris + tactile
        let signaturePadDetenteur = new SignaturePad(canvasDetenteur, {
            minWidth: 0.5,
            maxWidth: 2.5,
            penColor: 'rgb(0, 0, 0)',
            backgroundColor: 'rgba(255, 255, 255, 0)'
        });
        
        let signaturePadOperateur = new SignaturePad(canvasOperateur, {
            minWidth: 0.5,
            maxWidth: 2.5,
            penColor: 'rgb(0, 0, 0)',
            backgroundColor: 'rgba(255, 255, 255, 0)'
        });

        function resizeCanvas() {
            let ratio = Math.max(window.devicePixelRatio || 1, 1);

            let displayWidthDet = canvasDetenteur.clientWidth;
            let displayHeightDet = canvasDetenteur.clientHeight;
            if(displayWidthDet > 0 && displayHeightDet > 0) {
                canvasDetenteur.width = displayWidthDet * ratio;
                canvasDetenteur.height = displayHeightDet * ratio;
                canvasDetenteur.getContext("2d").scale(ratio, ratio);
            }

            let displayWidthOp = canvasOperateur.clientWidth;
            let displayHeightOp = canvasOperateur.clientHeight;
            if(displayWidthOp > 0 && displayHeightOp > 0) {
                canvasOperateur.width = displayWidthOp * ratio;
                canvasOperateur.height = displayHeightOp * ratio;
                canvasOperateur.getContext("2d").scale(ratio, ratio);
            }
        }

        // Désactiver le resize après le premier chargement
        window.addEventListener("load", function () {
            resizeCanvas(); // Applique le redimensionnement une seule fois
            signaturePadDetenteur.clear();
            signaturePadOperateur.clear();
        });

        // Sauvegarder la signature au format Base64
        function saveSignature() {
            if (!signaturePadDetenteur.isEmpty()) {
                signatureInputDetenteur.value = signaturePadDetenteur.toDataURL();
            } else {
                signatureInputDetenteur.value = "";
            }

            if (!signaturePadOperateur.isEmpty()) {
                signatureInputOperateur.value = signaturePadOperateur.toDataURL();
            } else {
                signatureInputOperateur.value = "";
            }
        }

        // Empêcher le resize intempestif en bloquant les événements sur mobile
        canvasDetenteur.addEventListener("touchstart", function () {
            window.removeEventListener("resize", resizeCanvas);
        }, { passive: false });
        canvasOperateur.addEventListener("touchstart", function () {
            window.removeEventListener("resize", resizeCanvas);
        }, { passive: false });

        // Gestion du bouton Effacer Opérateur
        clearButtonOperateur.addEventListener("click", function () {
            signaturePadOperateur.clear();
            signatureInputOperateur.value = "";
            signaturePadOperateur.on();
            valideButtonOperateur.disabled = false;
            valideButtonOperateur.style.background = "";
            valideButtonOperateur.innerHTML = '<i class="bi bi-check-circle"></i> Valider la signature';
        });

        // Gestion du bouton Effacer Détenteur
        clearButtonDetenteur.addEventListener("click", function () {
            signaturePadDetenteur.clear();
            signatureInputDetenteur.value = "";
            signaturePadDetenteur.on();
            valideButtonDetenteur.disabled = false;
            valideButtonDetenteur.style.background = "";
            valideButtonDetenteur.innerHTML = '<i class="bi bi-check-circle"></i> Valider la signature';
        });

        // Gestion du bouton Valider Opérateur
        valideButtonOperateur.addEventListener("click", function () {
            if (signaturePadOperateur.isEmpty()) {
                showSignatureError("Une signature de l'opérateur est requise pour valider.");
                return;
            }
            signaturePadOperateur.off();
            valideButtonOperateur.disabled = true;
            valideButtonOperateur.style.background = "linear-gradient(135deg, #64748b 0%, #334155 100%)";
            valideButtonOperateur.innerHTML = '<i class="bi bi-check-all"></i> Signature validée';
            valideButtonOperateur.style.boxShadow = "none";
        });

        // Gestion du bouton Valider Détenteur
        valideButtonDetenteur.addEventListener("click", function () {
            if (signaturePadDetenteur.isEmpty()) {
                showSignatureError("Une signature du détenteur est requise pour valider.");
                return;
            }
            signaturePadDetenteur.off();
            valideButtonDetenteur.disabled = true;
            valideButtonDetenteur.style.background = "linear-gradient(135deg, #64748b 0%, #334155 100%)";
            valideButtonDetenteur.innerHTML = '<i class="bi bi-check-all"></i> Signature validée';
            valideButtonDetenteur.style.boxShadow = "none";
        });

        function showSignatureError(message) {
            const errorMsg = document.createElement("div");
            errorMsg.className = "alert alert-danger d-flex align-items-center";
            errorMsg.style.cssText = "animation: slideDown 0.3s ease-out;";
            errorMsg.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i><strong>Erreur :</strong>&nbsp${message}`;
            errorMsg.id = "validation-signature-error";
            
            const existingError = document.getElementById("validation-signature-error");
            if (existingError) existingError.remove();
            
            form.insertBefore(errorMsg, form.firstChild);
            errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
            
            setTimeout(() => {
                errorMsg.remove();
            }, 10000);
        }

        // Intercepter l'envoi du formulaire pour charger le Base64 dans les inputs cachés
        form.addEventListener("submit", function (event) {
            saveSignature(); 
        });

        // === CORRECTION DE L'ERREUR DE FOCUS SUR LES ACCORDÉONS FERMÉS ===
        // Si un champ requis n'est pas rempli, on capte l'événement 'invalid'
        // On ouvre automatiquement l'accordéon parent pour que le navigateur puisse faire le focus
        const requiredInputs = form.querySelectorAll('input, select, textarea');
        requiredInputs.forEach(input => {
            input.addEventListener('invalid', function () {
                const accordionCollapse = this.closest('.accordion-collapse');
                
                // Si le champ est dans un accordéon et qu'il n'est pas ouvert
                if (accordionCollapse && !accordionCollapse.classList.contains('show')) {
                    // Utiliser l'API Bootstrap pour l'ouvrir
                    const bsCollapse = new bootstrap.Collapse(accordionCollapse, {
                        toggle: false
                    });
                    bsCollapse.show();
                    
                    // Optionnel : faire défiler la page pour bien voir l'erreur
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: "smooth", block: "center" });
                    }, 350); // Le délai permet d'attendre l'ouverture visuelle de l'accordéon
                }
            });
        });

    });
</script>

</body>
</html>
@endif