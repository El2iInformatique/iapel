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
    <title>Formulaire CERFA 15497-04</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
</head>
<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Fiche d'intervention cerfa_15497 n°{{ $uid }}</div>
                    <div class="card-body">

                        <!-- Formulaire -->
                        <form action="{{ route('bi.submit', ['client' => $client, 'document' => $document, 'uid' => $uid]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                                <h5 class="mt-4">[1] Opérateur</h5>
                                <hr>
                                <!-- Opérateur  -->
                                <div class="mb-3">
                                    <textarea class="form-control" id="operateur" name="operateur" rows="5" required>{{ $data['operateur'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                <label for="numero_attestation_capacite" class="form-label">Numéro d'attestation de capacité :</label>
                                    <input type="text" class="form-control" id="numero_attestation_capacite" name="numero_attestation_capacite" value="{{ old('numero_anumero_attestation_capacitettestation') }}">
                                </div>

                                <h5 class="mt-4">[2] Détenteur</h5>
                                <hr>
                                <!-- Détenteur  -->
                                <div class="mb-3">
                                    <textarea class="form-control" id="detenteur" name="detenteur" rows="5" required>{{ $data['detenteur'] ?? '' }}</textarea>
                                </div>

                                <h5 class="mt-4">[3] Equipement concerné</h5>
                                <hr>
                                <!-- Identification & Dénomination du fluide -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="identification" class="form-label">Identification :</label>
                                        <input type="text" class="form-control" id="identification" name="identification" value="{{ old('identification') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="denomination" class="form-label">Dénomination du fluide :</label>
                                        <input type="text" class="form-control" id="denomination" name="denomination" value="{{ old('denomination') }}">
                                    </div>
                                </div>

                                <!--  Charge Totale & Tonnage equivalent CO² -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="charge" class="form-label">Charge totale (en kg) :</label>
                                        <input type="text" class="form-control" id="charge" name="charge" value="{{ old('charge') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tonnage" class="form-label">Tonnage équivalent CO² (t. éq CO2) :</label>
                                        <input type="text" class="form-control" id="tonnage" name="tonnage" value="{{ old('tonnage') }}">
                                    </div>
                                </div>
                                
                                <h5 class="mt-4">[4] Nature de l'intervention</h5>
                                <hr>
                                <!-- Cases à cocher en 2 colonnes -->
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
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="nature_intervention" value="autre" id="autre">
                                                <label class="form-check-label" for="autre">Autre (préciser)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="autre_valeur" name="autre_valeur" value="{{ old('autre_valeur') }}">
                                        </div>
                                    </div>   
                                </div>

                                <h5 class="mt-4">[5] Détecteur manuel de fuite</h5>
                                <hr>
                                <!-- Identification & controlé le -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="identification_controle" class="form-label">Identification :</label>
                                        <input type="text" class="form-control" id="identification_controle" name="identification_controle" value="{{ old('identification_controle') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date_controle" class="form-label">Contrôlé le :</label>
                                        <input type="date" class="form-control" id="date_controle" name="date_controle" value="{{ old('date_controle', date('Y-m-d')) }}">
                                    </div>
                                </div>

                                <h5 class="mt-4">[6] Présence d'un système permanent de détection de fuites</h5>
                                <hr>
                                <!-- Présence d'un système permanent de détection de fuites -->
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
                                    <div class="col-md-6">
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

                                <!-- Gestion des fuites -->
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
                                        <textarea class="form-control" id="localisation_fuite_1" name="localisation_fuite_1" rows="2"></textarea>
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
                                        <textarea class="form-control" id="localisation_fuite_2" name="localisation_fuite_2" rows="2"></textarea>
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
                                        <textarea class="form-control" id="localisation_fuite_3" name="localisation_fuite_3" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Réparation de la fuite n°2 :</label>
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

                                <!-- Manipulation fluide -->
                                <h5 class="mt-4">[11] Manipulation du fluide frigorigène</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                         <div class="mb-3">
                                            <label for="quantite_chargee_totale" class="form-label">Quantité chargée totale (A+B+C) (kg) :</label>
                                            <input type="text" class="form-control" id="quantite_chargee_totale" name="quantite_chargee_totale" value="{{ old('quantite_chargee_totale') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="quantite_chargee_A" class="form-label">A - Dont fluide vierge (kg) :</label>
                                            <input type="text" class="form-control" id="quantite_chargee_A" name="quantite_chargee_A" value="{{ old('quantite_chargee_A') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="fluide_A" class="form-label">Dénomination du fluide chargé si changement :</label>
                                            <input type="text" class="form-control" id="fluide_A" name="fluide_A" value="{{ old('fluide_A') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="quantite_chargee_B" class="form-label">B - Dont fluide recyclé (fluide récupéré et réintrooduit) (kg) :</label>
                                            <input type="text" class="form-control" id="quantite_chargee_B" name="quantite_chargee_B" value="{{ old('quantite_chargee_B') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="quantite_chargee_C" class="form-label">C - Dont fluide régénéré :</label>
                                            <input type="text" class="form-control" id="quantite_chargee_C" name="quantite_chargee_C" value="{{ old('quantite_chargee_C') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                         <div class="mb-3">
                                            <label for="quantite_recuperee_totale" class="form-label">Quantité de fluide récupérée totale (D+E) (kg) :</label>
                                            <input type="text" class="form-control" id="quantite_recuperee_totale" name="quantite_recuperee_totale" value="{{ old('quantite_recuperee_totale') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="quantite_recuperee_D" class="form-label">D - Dont fluide destiné au traitement (kg) :</label>
                                            <input type="text" class="form-control" id="quantite_recuperee_D" name="quantite_recuperee_D" value="{{ old('quantite_recuperee_D') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="BSFF" class="form-label">Si connu, numéro du BSFF (Trackdéchets) :</label>
                                            <input type="text" class="form-control" id="BSFF" name="BSFF" value="{{ old('BSFF') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="quantite_recuperee_E" class="form-label">E - Dont fluide conservé pour réutilisation (réintroduction) :</label>
                                            <input type="text" class="form-control" id="quantite_recuperee_E" name="quantite_recuperee_E" value="{{ old('quantite_recuperee_E') }}">
                                        </div>
                                         <div class="mb-3">
                                            <label for="identification_E" class="form-label">Identification du ou des contenants :</label>
                                            <input type="text" class="form-control" id="identification_E" name="identification_E" value="{{ old('identification_E') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Manipulation fluide -->
                                <h5 class="mt-4">[12] Dénomination ADR/RID</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mt-3">Rubrique Déchets : 14 06 01* – CFC, HCFC, HFC, mélange HFC/HFO – Fluides non-inflammables</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fluide_non_inflammable" value="UN1078" id="UN1078">
                                            <label class="form-check-label" for="UN1078">UN 1078, Déchet Gaz frigorifique NSA (Gaz réfrigérant, NSA), 2.2 (C/E)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fluide_non_inflammable" value="autre_cas_non_inflammable" id="autre_cas_non_inflammable">
                                            <label class="form-check-label" for="autre_cas_non_inflammable">Autre cas de fluides frigorigènes non-inflammables :</label>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="autre_fluide_non_inflammable" name="autre_fluide_non_inflammable" value="{{ old('autre_fluide_non_inflammable') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mt-3">Rubrique Déchets : 16 05 04* – HFC-mélange HFC/HFO – Fluides inflammables</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fluide_inflammable" value="UN3161" id="UN3161">
                                            <label class="form-check-label" for="UN3161">UN 3161, Déchet Gaz liquéfié inflammable NSA, 2.1 (B/D)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fluide_inflammable" value="autre_cas_inflammable" id="autre_cas_non_inflammable">
                                            <label class="form-check-label" for="autre_cas_inflammable">Autre cas de fluides frigorigènes inflammables :</label>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="autre_fluide_inflammable" name="autre_fluide_inflammable" value="{{ old('autre_fluide_non_inflammable') }}">
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mt-4">[13] Installation prévue de destination </h5>
                                <hr>
                                <div class="mb-3">
                                    <textarea class="form-control" id="installation_destination_fluide" name="installation_destination_fluide" rows="2" ></textarea>
                                </div>

                                <h5 class="mt-4">[14] Observations : </h5>
                                <hr>
                                <div class="mb-3">
                                    <textarea class="form-control" id="observations" name="observations" rows="5" ></textarea>
                                </div>

                                <!-- Signature -->
                                <h5 class="mt-4">Signature</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="mt-3">Opérateur</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nom_signataire_operateur" class="form-label">Nom du signataire :</label>
                                            <input type="text" class="form-control" id="nom_signataire_operateur" name="nom_signataire_operateur" value="{{ old('nom_signataire_operateur') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="qualite_signataire_operateur" class="form-label">Qualité du signataire :</label>
                                            <input type="text" class="form-control" id="qualite_signataire_operateur" name="qualite_signataire_operateur" value="{{ old('qualite_signataire_operateur') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Signature :</label>
                                            <canvas id="signature-pad-operateur" class="border" style="width: 100%; height: 200px;"></canvas>
                                            <input type="hidden" name="signature-operateur" id="signature-operateur">
                                            <button type="button" class="btn btn-secondary mt-2" id="clear-signature-operateur">Effacer</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="mt-3">Détenteur</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nom_signataire_detenteur" class="form-label">Nom du signataire :</label>
                                            <input type="text" class="form-control" id="nom_signataire_detenteur" name="nom_signataire_detenteur" value="{{ old('nom_signataire_detenteur') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="qualite_signataire_detenteur" class="form-label">Qualité du signataire :</label>
                                            <input type="text" class="form-control" id="qualite_signataire_detenteur" name="qualite_signataire_detenteur" value="{{ old('qualite_signataire_detenteur') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Signature :</label><canvas id="signature-pad-detenteur" class="border" style="width: 100%; height: 200px;"></canvas>
                                            <input type="hidden" name="signature-detenteur" id="signature-detenteur">
                                            <button type="button" class="btn btn-secondary mt-2" id="clear-signature-detenteur">Effacer</button>
                                        </div>
                                    </div>
                                </div>


                            <!-- Validation du formulaire -->
                            <button type="submit" class="btn btn-primary w-100">Valider et générer le PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

    <script>
    /*
    document.getElementById("imageUpload").addEventListener("change", function(event) {
        let reader = new FileReader();
        reader.onload = function() {
            let imgPreview = document.getElementById("imagePreview");
            imgPreview.src = reader.result;
            imgPreview.classList.remove("d-none");
        };
        reader.readAsDataURL(event.target.files[0]);
    });        
    */

    document.addEventListener("DOMContentLoaded", function () {
        let autreCheckbox = document.getElementById("autre");
        let autreValeur = document.getElementById("autre_valeur");

        let localisationFuitesRadioOui = document.getElementById("constat_oui");
        let localisationFuitesRadioNon = document.getElementById("constat_non");
        let localisationFuitesValeur = document.getElementById("localisation_fuites");
        
        let canvasDetenteur = document.getElementById("signature-pad-detenteur");
        let signatureInputDetenteur = document.getElementById("signature-detenteur");
        let clearButtonDetenteur = document.getElementById("clear-signature-detenteur");
        let canvasOperateur = document.getElementById("signature-pad-operateur");
        let signatureInputOperateur = document.getElementById("signature-operateur");
        let clearButtonOperateur = document.getElementById("clear-signature-operateur");
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

        

        // Créer la signature avec gestion souris + tactile
        let signaturePadDetenteur = new SignaturePad(canvasDetenteur, {
            backgroundColor: 'rgba(255, 255, 255, 0)', // Fond transparent
            penColor: "black", // Couleur du stylo
            backgroundColor: "white"
        });
        let signaturePadOperateur = new SignaturePad(canvasOperateur, {
            backgroundColor: 'rgba(255, 255, 255, 0)', // Fond transparent
            penColor: "black", // Couleur du stylo
            backgroundColor: "white"
        });

        function resizeCanvas() {
            let ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvasDetenteur.width = canvasDetenteur.offsetWidth * ratio;
            canvasDetenteur.height = canvasDetenteur.offsetHeight * ratio;
            canvasDetenteur.getContext("2d").scale(ratio, ratio);
            
            canvasOperateur.width = canvasOperateur.offsetWidth * ratio;
            canvasOperateur.height = canvasOperateur.offsetHeight * ratio;
            canvasOperateur.getContext("2d").scale(ratio, ratio);
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
                console.log("Signature détenteur enregistrée !");
            } else {
                signatureInputDetenteur.value = "";
                console.log("Aucune signature détenteur détectée !");
            }

            if (!signaturePadOperateur.isEmpty()) {
                signatureInputOperateur.value = signaturePadOperateur.toDataURL();
                console.log("Signature opérateur enregistrée !");
            } else {
                signatureInputOperateur.value = "";
                console.log("Aucune signature opérateur détectée !");
            }
        }

        // Empêcher le resize intempestif en bloquant les événements sur mobile
        canvasDetenteur.addEventListener("touchstart", function () {
            window.removeEventListener("resize", resizeCanvas);
        }, { passive: false });
        canvasOperateur.addEventListener("touchstart", function () {
            window.removeEventListener("resize", resizeCanvas);
        }, { passive: false });

        // Effacer la signature
        clearButtonDetenteur.addEventListener("click", function () {
            signaturePadDetenteur.clear();
            signatureInputDetenteur.value = "";
        });
        clearButtonOperateur.addEventListener("click", function () {
            signaturePadOperateur.clear();
            signatureInputOperateur.value = "";
        });
        
        // S'assurer que la signature est bien enregistrée avant soumission
        form.addEventListener("submit", function (event) {
            saveSignature(); // Enregistre la signature avant l'envoi
        });
    });
</script>

</body>
</html>
@endif