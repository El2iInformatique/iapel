<?php
    //$pdfPath = storage_path('app/public/cerfa_13948-03/'.$uid.'/'.$uid.'.pdf');
    $pdfPath = "pas gérer pour l'instant"
?>

@if(file_exists($pdfPath))
    <script>
        window.location.href = "{{ route('pdf.view', ['document' => 'cerfa_13948-03','uid' => $uid]) }}";
    </script>
@else
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Formulaire d'attestation simplifiée</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
</head>
<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Attestation simplifiée cerfa_13948-03</div>
                    <div class="card-body">

                        <!-- Formulaire -->
                        <form action="{{ route('bi.submit', ['client' => $client, 'document' => 'cerfa_13948-03', 'uid' => $uid]) }}" method="POST" enctype="multipart/form-data">
                           
                         @csrf

                            <div class="accordion" id="accordion_attestation">
                                <!-- Première section -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_client">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_client" aria-expanded="true" aria-controls="accordion_collapse_client">
                                        <i class="bi bi-info-circle info-icon" id="info_client"></i>
                                            1 - Identité du client ou de son représentant
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_client" class="accordion-collapse collapse show" aria-labelledby="accordion_header_client" data-bs-parent="#accordion_attestation">
                                        <div class="accordion-body">
                                            <p>Je soussigné(e)</p>   
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="nom" class="form-label">Nom :</label>
                                                    <input type="text" class="form-control" id="nom" name="nom" value="{{ $data['nom'] ?? '' }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prenom" class="form-label">Prénom :</label>
                                                    <input type="text" class="form-control" id="prenom" name="prenom" value="{{ $data['prenom'] ?? '' }}">
                                                </div>
                                            </div> 
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label for="adresse" class="form-label">Adresse :</label>
                                                    <input type="text" class="form-control" id="adresse" name="adresse" value="{{ $data['adresse'] ?? '' }}">
                                                </div>
                                            </div> 
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code_postal" class="form-label">Code postal :</label>
                                                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="{{ $data['code_postal'] ?? '' }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="commune" class="form-label">Ville :</label>
                                                    <input type="text" class="form-control" id="commune" name="commune" value="{{ $data['commune'] ?? '' }}">
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                </div>

                                <!-- Deuxième section -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_locaux">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_locaux" aria-expanded="false" aria-controls="accordion_collapse_locaux">
                                            <i class="bi bi-info-circle info-icon" id="info_locaux"></i>
                                            2 - Nature des locaux
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_locaux" class="accordion-collapse collapse" aria-labelledby="accordion_header_locaux" data-bs-parent="#accordion_attestation">
                                        <div class="accordion-body">
                                            <p>J'atteste que les travaux à réaliser portent sur un immeuble achevé depuis plus de deux ans à la date de commencement des travaux et affecté à l'habitation à l'issue de ces travaux :</p>    
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_type" value="maison" id="maison">
                                                        <label class="form-check-label" for="maison">maison ou immeuble individuel</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_type" value="immeuble" id="immeuble">
                                                        <label class="form-check-label" for="immeuble">immeuble collectif</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_type" value="appartement" id="appartement">
                                                        <label class="form-check-label" for="appartement">appartenment individuel</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_type" value="autre" id="autre">
                                                        <label class="form-check-label" for="autre">autre <span class="small-text">(précisez la nature du local à usage d'habitation)</span></label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="nature_locaux_type_autre_valeur" name="nature_locaux_type_autre_valeur">
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <p>Les travaux sont réalisés dans :</p>    
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_affectation" value="affectation_1" id="affectation_1">
                                                        <label class="form-check-label" for="affectation_1">un local affecté exclusivement ou principalement à l'habitation</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_affectation" value="affectation_2" id="affectation_2">
                                                        <label class="form-check-label" for="affectation_2">des pièces affectées exclusivement à l'habitation situées dans un local affecté pour moins de 50 % à cet usage</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_affectation" value="affectation_3" id="affectation_3">
                                                        <label class="form-check-label" for="affectation_3">des parties communes de locaux affectés exclusivement ou principalement à l'habitation dans une proportion de (<input type="text" size="4" id="milliemes" name="milliemes">) millièmes de l'immeuble</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_affectation" value="affectation_4" id="affectation_4">
                                                        <label class="form-check-label" for="affectation_4">un local antérieurement affecté à un  usage autre que d'habitation et transformé à cet usage</label>
                                                    </div>                                                
                                                </div>
                                            </div>
                                            
                                            <hr>
                                            <div class="row">
                                                <div class="mb-3">
                                                    <label for="adresse_travaux" class="form-label">Adresse <span class="small-text">(Si différente de l'adresse indiquée dans le cadre 1)</span> :</label>
                                                    <input type="text" class="form-control" id="adresse_travaux" name="adresse_travaux">
                                                </div>
                                            </div> 
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code_postal_travaux" class="form-label">Code postal :</label>
                                                    <input type="text" class="form-control" id="code_postal_travaux" name="code_postal_travaux">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="commune_travaux" class="form-label">Ville :</label>
                                                    <input type="text" class="form-control" id="commune_travaux" name="commune_travaux">
                                                </div>
                                            </div>    
                                            
                                            <hr>
                                            <p>Dont je suis :</p>    
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_status" value="proprietaire" id="proprietaire">
                                                        <label class="form-check-label" for="proprietaire">propriétaire</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_status" value="locataire" id="locataire">
                                                        <label class="form-check-label" for="locataire">locataire</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nature_locaux_status" value="status_autre" id="status_autre">
                                                        <label class="form-check-label" for="status_autre">autre <span class="small-text">(précisez votre qualité)</span> :</label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="nature_locaux_status_autre_valeur" name="nature_locaux_status_autre_valeur">
                                                    </div>
                                                </div>
                                            </div>   
                                        </div>
                                    </div>
                                </div>

                                <!-- Troisième section -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_travaux">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_travaux" aria-expanded="false" aria-controls="accordion_collapse_travaux">
                                            <i class="bi bi-info-circle info-icon" id="info_travaux"></i>
                                            3 - Nature des travaux
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_travaux" class="accordion-collapse collapse" aria-labelledby="accordion_header_travaux" data-bs-parent="#accordion_attestation">
                                        <div class="accordion-body">
                                            <p>J'atteste  que  sur  la  période  de  deux  ans  précédant  ou  suivant  la  réalisation  des  travaux  décrits  dans  la  présente  attestation,  les travaux :</p>    

                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_1" id="travaux_1">
                                                    <label class="form-check-label" for="travaux_1">n'affectent  ni  les  fondations,  ni  les  éléments,  hors  fondations,  déterminant  la  résistance  et  la  rigidité  de  l'ouvrage,  ni la consistance des façades (hors ravalement).</label>
                                                </div>    
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_2" id="travaux_2">
                                                    <label class="form-check-label" for="travaux_2">n'affectent pas plus de cinq des six éléments de second oeuvre suivants :</label>

                                                    <div class="col">
                                                        <h6><span class="small-text">Cochez  les  cases  correspondant  aux  éléments  affectés :</span> </h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_1" id="travaux_2_details_1">
                                                            <label class="form-check-label" for="travaux_2_details_1"><span class="small-text">planchers  qui  ne  déterminent  pas  la résistance ou  la rigidité  de l'ouvrage</span></label>
                                                        </div>  
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_2" id="travaux_2_details_2">
                                                            <label class="form-check-label" for="travaux_2_details_2"><span class="small-text">huisseries extérieures</span></label>
                                                        </div>  
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_3" id="travaux_2_details_3">
                                                            <label class="form-check-label" for="travaux_2_details_3"><span class="small-text">cloisons intérieures</span></label>
                                                        </div>  
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_4" id="travaux_2_details_4">
                                                            <label class="form-check-label" for="travaux_2_details_4"><span class="small-text">installations sanitaires et de plomberie</span></label>
                                                        </div>  
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_5" id="travaux_2_details_5">
                                                            <label class="form-check-label" for="travaux_2_details_5"><span class="small-text">installations électriques</span></label>
                                                        </div>  
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="travaux_2_details[]" value="travaux_2_details_6" id="travaux_2_details_6">
                                                            <label class="form-check-label" for="travaux_2_details_6"><span class="small-text">système de chauffage (pour les immeubles situés en métropole)</span></label>
                                                        </div>  
                                                        <span class="small-text">NB : tous autres travaux sont sans incidence sur le bénéfice du taux réduit.</span>
                                                    </div>


                                                </div>    
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_3" id="travaux_3">
                                                    <label class="form-check-label" for="travaux_3">n'entraînent pas une augmentation de la surface de plancher de la construction  existante supérieure à 10%.</label>
                                                </div>    
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_4" id="travaux_4">
                                                    <label class="form-check-label" for="travaux_4">ne consistent pas en une surélévation ou une addition de construction.</label>
                                                </div>  
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_5" id="travaux_5">
                                                    <label class="form-check-label" for="travaux_5">J'atteste  que  les  travaux  ont  la  nature  de  travaux  d'amélioration  de  la  qualité  énergétique  portant  sur  la  fourniture,  la  pose,
                                                            l'installation  ou  l'entretien  des matériaux,  appareils  et  équipements mentionnés  au  1  de  l'article  200  quater  du  code  général  des
                                                            impôts  (CGI)  et  respectant    les  caractéristiques  techniques  et  les  critères  de  performances minimales  fixés  par  l'article  18  bis  de
                                                            l'annexe IV au CGI dans sa rédaction issue de l'arrêté du 29 décembre 2013.</label>
                                                </div>      
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="travaux[]" value="travaux_6" id="travaux_6">
                                                    <label class="form-check-label" for="travaux_6">J'atteste  que  les  travaux  ont  la  nature  de  travaux  induits  indissociablement  liés  à  des  travaux  d'amélioration  de  la  qualité
                                                    énergétique soumis au taux de TVA de 5,5 %.</label>
                                                </div>  
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quatrième section -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="accordion_header_conservation">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_collapse_conservation" aria-expanded="false" aria-controls="accordion_collapse_conservation">
                                            <i class="bi bi-info-circle info-icon" id="info_conservation"></i>
                                            4 - Conservation de l'attestation et des pièces justificatives
                                        </button>
                                    </h2>
                                    <div id="accordion_collapse_conservation" class="accordion-collapse collapse" aria-labelledby="accordion_header_conservation" data-bs-parent="#accordion_attestation">
                                        <div class="accordion-body">
                                            <p>Je conserve une copie de cette attestation ainsi que de  toutes  les  factures ou notes émises par  les entreprises prestataires  jusqu'au
                                            31 décembre de la cinquième année suivant la réalisation des travaux et m'engage à en produire 
                                            une copie à l'administration fiscale sur sa demande.</p>    
                                        </div>
                                    </div>
                                </div>

                                            
                            </div>
                            <hr>
                            <div class="alert alert-warning" id="warning_attestation">
                                Si  les mentions portées  sur  l'attestation  s'avèrent  inexactes de votre  fait et ont eu pour conséquence  l'application erronée du  taux
                                réduit de la TVA, vous êtes solidairement tenu au paiement du complément de taxe résultant de la différence entre le montant de la
                                taxe due (TVA au taux de 20 % ou 10 %) et le montant de la TVA effectivement payé, TVA au taux de :
                                <br>
                                -  10 %  pour  les  travaux  d'amélioration, de transformation, daménagement  et  d'entretien  portant  sur  des  locaux  à  usage
                                d'habitation achevés depuis plus de 2 ans ;
                                <br>
                                -  5,5 % pour les travaux d'amélioration de la qualité énergétique des  locaux à usage d'habitation achevés depuis plus de 2 ans
                                ainsi que sur les travaux induits qui leur sont indissociablement liés.
                            </div>
                            
                            <hr>
                            <!-- Gestion de la signature -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fait_a" class="form-label">Fait à :</label>
                                    <input type="text" class="form-control" id="fait_a" name="fait_a" value="{{ $data['commune'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fait_le" class="form-label">le :</label>
                                    <input type="date" class="form-control" id="fait_le" name="fait_le" value="{{ date('Y-m-d') }}">
                                </div>
                            </div> 
                            <div class="col">     
                                <label class="form-label">Signature du client ou de son représentant :</label>
                                <canvas id="signature-pad" class="border" style="width: 100%; height: 300px;"></canvas>
                                <input type="hidden" name="signature" id="signature">
                                <button type="button" class="btn btn-secondary mt-2" id="clear-signature">Effacer</button>
                            </div>
                            
                            <hr>
                            <!-- Validation du formulaire -->
                            <button type="submit" class="btn btn-primary w-100">Valider et générer le PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'information -->
    <div class="modal fade" id="info_modal_client" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Identité du client ou de son représentant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    L’attestation est remplie par la personne qui fait effectuer les travaux 
                    (propriétaire occupant, propriétaire bailleur, locataire, syndicat de copropriétaires, etc.).
                    C’est à elle de justifier qu’elle a respecté les mentions portées sur l’attestation.
                    Si l'administration conteste les informations portées sur l'attestation, 
                    c'est l'administration qui devra apporter la preuve que celles-ci sont inexactes.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="info_modal_locaux" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Nature des locaux</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    Pour  bénéficier  des  taux  réduits  de  la  TVA,  les  travaux  doivent  porter  sur  des  locaux  à  usage
                    d'habitation achevés depuis plus de deux ans. Les taux réduits sont également applicables aux travaux qui ont pour objet d'affecter
                    principalement à un usage d'habitation un  local précédemment affecté à un autre usage  sauf  s'ils concourent à  la production d'un
                    immeuble neuf.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="info_modal_travaux" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Nature des travaux</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    Cochez les cases correspondant à votre situation.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="info_modal_conservation" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Conservation de l'attestation et des pièces justificatives</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    L'attestation, une fois complétée, datée et signée,
                    doit être remise au prestataire effectuant les travaux, avant leur commencement (ou au plus tard avant la facturation). Lorsqu'il y a
                    plusieurs prestataires, un original de l'attestation doit être remis à chacun d'entre eux.
                    Vous devez conserver une copie de l'attestation ainsi que lensemble des factures ou notes émises par le(s) prestataire(s) ayant réalisé
                    des travaux jusqu'au 31 décembre de la cinquième année suivant leur réalisation. En cas de réalisation de travaux d'amélioration de
                    la qualité énergétique, vous devez conserver  la  facture comportant  les mentions prévues au b du 6 de  l'article 200 quater du CGI
                    (Cette facture comporte, outre les mentions prévues à l'article 289 : le lieu de réalisation des travaux ou du diagnostic de performance
                    énergétique ;  la nature de ces  travaux  ainsi que  la désignation,  le montant  et,  le  cas  échéant,  les  caractéristiques  et  les  critères de
                    performances, mentionnés  à  la deuxième phrase du premier  alinéa du 2, des  équipements, matériaux  et  appareils ;  dans  le  cas  de
                    l'acquisition  et  de  la  pose  de matériaux  d'isolation  thermique  des  parois  opaques,  la  surface  en mètres  carrés  des  parois  opaques
                    isolées,  en  distinguant  ce  qui  relève  de  l'isolation  par  l'extérieur  de  ce  qui  relève  de  l'isolation  par  l'intérieur ;  dans  le  cas  de
                    l'acquisition  d'équipements  de  production  d'énergie  utilisant  une source d'énergie renouvelable,  la  surface  en  mètres  carrés  des
                    équipements  de  production  d'énergie  utilisant  l'énergie  solaire  thermique ;  lorsque  les  travaux  d'installation  des  équipements,
                    matériaux  et  appareils  y  sont  soumis,  les  critères  de  qualification  de  l'entreprise).  Elles  devront  en  effet  être  produites  si
                    l'administration vous demande de justifier de l'application du taux réduit de la TVA.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            let form = document.querySelector("form");

            const infoIcon_client = document.getElementById("info_client");
            const infoModal_client = new bootstrap.Modal(document.getElementById("info_modal_client"));
            infoIcon_client.addEventListener("click", function(event) {
                infoModal_client.show(); // Ouvre la modal manuellement
            });

            const infoIcon_locaux = document.getElementById("info_locaux");
            const infoModal_locaux = new bootstrap.Modal(document.getElementById("info_modal_locaux"));
            infoIcon_locaux.addEventListener("click", function(event) {
                infoModal_locaux.show(); // Ouvre la modal manuellement
            });
            
            const infoIcon_travaux = document.getElementById("info_travaux");
            const infoModal_travaux = new bootstrap.Modal(document.getElementById("info_modal_travaux"));
            infoIcon_travaux.addEventListener("click", function(event) {
                infoModal_travaux.show(); // Ouvre la modal manuellement
            });

            const infoIcon_conservation = document.getElementById("info_conservation");
            const infoModal_conservation = new bootstrap.Modal(document.getElementById("info_modal_conservation"));
            infoIcon_conservation.addEventListener("click", function(event) {
                infoModal_conservation.show(); // Ouvre la modal manuellement
            });
            
            let canvas = document.getElementById("signature-pad");
            let signatureInput = document.getElementById("signature");
            let clearButton = document.getElementById("clear-signature");
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
                if (!signaturePad.isEmpty()) {
                    signatureInput.value = signaturePad.toDataURL();
                    console.log("Signature enregistrée !");
                } else {
                    signatureInputDetenteur.value = "";
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