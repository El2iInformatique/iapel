<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\TokenLinksRapport;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


/**
 * @class PdfController
 * @brief Gère la génération, le remplissage et l'affichage des documents PDF liés aux rapports et attestations.
 *
 * Ce contrôleur centralise toutes les opérations relatives aux fichiers PDF :
 * - Génération dynamique à partir de modèles prédéfinis.
 * - Remplissage automatique de formulaires (Cerfa, attestations TVA, bulletins d'intervention...).
 * - Téléchargement et affichage sécurisés des documents liés à un token.
 * - Upload et gestion des devis PDF certifiés.
 *
 * @package App\Http\Controllers
 * @version 2.0
 * @author Équipe de développement IAPEL
 * @since 1.0
 * @note Ce contrôleur utilise la librairie TCPDF/FPDI pour manipuler les fichiers PDF.
 * @warning Les méthodes de génération nécessitent des modèles PDF préexistants dans le storage.
 */
class PdfController extends Controller
{
    /**
     * @brief Affiche un document PDF à partir d'un token d'accès.
     *
     * Cette méthode :
     * - Recherche le fichier PDF correspondant au token fourni dans TokenLinksRapport.
     * - Vérifie l'existence du fichier JSON associé contenant les métadonnées.
     * - Extrait les informations du client, document et UID.
     * - Retourne la vue d'affichage PDF avec les données nécessaires.
     *
     * @param string $token Jeton unique associé au document PDF pour l'identification sécurisée.
     *
     * @return mixed Vue d'affichage du PDF ou erreur 404.
     *
     * @throws Exception Si aucun token valide n'est trouvé dans la base.
     * @throws Exception Si le fichier JSON associé n'existe pas.
     *
     * @note Cette méthode est utilisée pour l'affichage dans le navigateur, pas pour le téléchargement direct.
     * @see TokenLinksRapport Pour la gestion des tokens et leurs liens.
     * @par Exemple:
     * GET /pdf/show/abc123 pour afficher le PDF associé au token "abc123".
     */
    public function show($token)
    {

        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        if (!file_exists($filePath)) {
            return abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];
        

        return view('pdf', compact('client','document', 'uid'));        
    }

    /**
     * @brief Génère et télécharge un PDF personnalisé à partir de données JSON.
     *
     * Cette méthode traite les requêtes de génération de PDF pour différents types de documents :
     * - Crée le répertoire de stockage si nécessaire.
     * - Sauvegarde les données sous forme de fichier JSON.
     * - Génère le PDF en remplissant les champs du modèle avec les données fournies.
     * - Retourne le fichier PDF généré en téléchargement.
     *
     * Types de documents supportés :
     * - cerfa_15497-03 : Formulaire CERFA pour fluides frigorigènes
     * - 1301-SD : Attestation TVA pour travaux
     *
     * @param Request $request Requête HTTP contenant les données JSON du formulaire.
     *                        Doit contenir : uid, document, et les champs spécifiques au type.
     *
     * @return mixed Téléchargement du PDF généré ou erreur JSON si le fichier source n'existe pas.
     *
     * @throws Exception Si une erreur survient lors de la création du fichier JSON.
     * @throws Exception Si le modèle PDF source est introuvable.
     *
     * @note Le PDF généré est "aplati" pour empêcher les modifications ultérieures.
     * @warning Cette méthode nécessite que les modèles PDF soient présents dans storage/app/public/.
     * @par Exemple:
     * POST avec JSON : {"uid": "12345", "document": "1301-SD", "nom": "Dupont", ...}
     */
    public function generateDownloadPDF(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $uid = $data['uid'];
        $document = $data['document'];
        
        // Création du JSON
        $folderPath = storage_path('app/public/' . $document . '/' . $uid);

        // Vérifier si le dossier existe, sinon le créer
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

        $jsonPath = $folderPath . '/'. $uid .'.json';
        try {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            
            if ($document == 'cerfa_15497-03') {
                $jsonData = [
                    'operateur' => $data['Operateur'] ?? '',
                    'detenteur' => $data['Detenteur'] ?? ''
                ];
            }
            elseif ($document == '1301-SD') {
                $jsonData = [
                    'nom' => $data['nom'] ?? '',
                    'prenom' => $data['prenom'] ?? '',
                    'adresse' => $data['adresse'] ?? '',
                    'commune' => $data['commune'] ?? '',
                    'code_postal' => $data['code_postal'] ?? ''
                ];
            }     

            file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du fichier JSON : ' . $e->getMessage());
        }

        // Création du PDF
        $pdfPath = storage_path('app/public/'.$document.'/'.$document.'.pdf'); // PDF d'origine
        $outputPath = storage_path('app/public/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // Lire le fichier JSON
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

        // Initialiser FPDI
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($pdfPath);
        $totalPages = $pdf->setSourceFile($pdfPath);
        for ($i = 1; $i <= $totalPages; $i++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0);

            if ($i == 1) {
                // Modification de la page n°1
                // Définir la police et la taille du texte
                $pdf->SetFont('helvetica', '', 8);

                $pdf->SetXY(28, 47);
                $pdf->Write(10, ($data['nom'] ?? ''));    

                $pdf->SetXY(110, 47);
                $pdf->Write(10, ($data['prenom'] ?? '')); 

                $pdf->SetXY(32, 50.6);
                $pdf->Write(10, ($data['adresse'] ?? '')); 

                $pdf->SetXY(112, 50.6);
                $pdf->Write(10, ($data['commune'] ?? '')); 

                $pdf->SetXY(168, 50.6);
                $pdf->Write(10, ($data['code_postal'] ?? '')); 
            }

        }

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        // Téléchargement du PDF
        return response()->download($outputPath, "{$uid}.pdf");
    }

    /**
     * @brief Génère une attestation TVA personnalisée pour les travaux de rénovation.
     *
     * Cette méthode gère la génération d'attestations TVA avec des fonctionnalités avancées :
     * - Support des requêtes JSON (POST) et paramètres URL (GET).
     * - Remplissage automatique des champs d'identité (nom, prénom, adresse).
     * - Gestion des cases à cocher pour le type de logement et l'affectation.
     * - Traitement des différents types de travaux avec leurs détails.
     * - Intégration de signatures numériques en base64.
     * - Formatage des dates au format français (d/m/Y).
     *
     * @param \Illuminate\Http\Request $request Requête HTTP contenant soit :
     *                                         - JSON (POST) : uid, document, client + données du formulaire
     *                                         - Query params (GET) : uid, document, client
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     *         Fichier PDF généré en affichage inline ou erreur JSON.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException Si le fichier JSON de données n'existe pas.
     * @throws \InvalidArgumentException Si les paramètres requis sont manquants.
     *
     * @note La signature est automatiquement convertie de base64 vers PNG et intégrée au PDF.
     * @note Les coordonnées de positionnement sont spécifiquement calibrées pour le modèle d'attestation TVA.
     * @warning Nécessite un fichier JSON préalablement créé contenant toutes les données du formulaire.
     * @see generateDownloadPDF() Pour la création initiale du fichier JSON de données.
     */
    public function generateAttestationTVA(Request $request)
    {
        if ($request->isJson()) {
            // Request en JSON provenant du POST
            $data = json_decode($request->getContent(), true);
            $uid = $data['uid'];
            $document = $data['document'];
            $client = $data['client'];
        } else {
            // Request provenant du GET
            $uid = $request->query('uid');
            $document = $request->query('document');
            $client = $request->query('client');
        }
        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // PDF d'origine
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // Lire le fichier JSON
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

         // Remplissage des champs avec des valeurs dynamiques
        $data = json_decode(file_get_contents($jsonPath), true);

        // Initialiser FPDI
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($pdfPath);
        $totalPages = $pdf->setSourceFile($pdfPath);
        for ($i = 1; $i <= $totalPages; $i++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0);

            if ($i == 1) {
                // Modification de la page n°1
                $pdf->SetFont('helvetica', '', 8);

                $pdf->SetXY(28, 47);
                $pdf->Write(10, ($data['nom'] ?? ''));    

                $pdf->SetXY(110, 47);
                $pdf->Write(10, ($data['prenom'] ?? '')); 

                $pdf->SetXY(32, 50.6);
                $pdf->Write(10, ($data['adresse'] ?? '')); 

                $pdf->SetXY(112, 50.6);
                $pdf->Write(10, ($data['commune'] ?? '')); 

                $pdf->SetXY(168, 50.6);
                $pdf->Write(10, ($data['code_postal'] ?? '')); 
                
                if ($data['nature_locaux_type'] == 'maison') {
                    $pdf->SetXY(19.5, 74.8);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_type'] == 'immeuble') {
                    $pdf->SetXY(84.5, 74.8);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_type'] == 'appartement') {
                    $pdf->SetXY(134.5, 74.8);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_type'] == 'autre') {
                    $pdf->SetXY(19.5, 80.6);
                    $pdf->Write(10, 'X'); 

                    $pdf->SetXY(96, 80);
                    $pdf->Write(10, $data['nature_locaux_type_autre_valeur']); 
                }

                
                if ($data['nature_locaux_affectation'] == 'affectation_1') {
                    $pdf->SetXY(19.5, 92);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_affectation'] == 'affectation_2') {
                    $pdf->SetXY(19.5, 97.8);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_affectation'] == 'affectation_3') {
                    $pdf->SetXY(19.5, 103.6);
                    $pdf->Write(10, 'X'); 

                    $pdf->SetXY(172, 103);
                    $pdf->Write(10, $data['milliemes']); 
                }
                else if ($data['nature_locaux_affectation'] == 'affectation_4') {
                    $pdf->SetXY(19.5, 112);
                    $pdf->Write(10, 'X'); 
                }

                $pdf->SetXY(34, 117);
                $pdf->Write(10, $data['adresse_travaux']); 
                $pdf->SetXY(104, 117);
                $pdf->Write(10, $data['commune_travaux']); 
                $pdf->SetXY(160, 117);
                $pdf->Write(10, $data['code_postal_travaux']); 

                if ($data['nature_locaux_status'] == 'proprietaire') {
                    $pdf->SetXY(36.5, 123.5);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_status'] == 'locataire') {
                    $pdf->SetXY(59.5, 123.5);
                    $pdf->Write(10, 'X'); 
                }
                else if ($data['nature_locaux_status'] == 'status_autre') {
                    $pdf->SetXY(84.5, 123.5);
                    $pdf->Write(10, 'X'); 
                    
                    $pdf->SetXY(130, 123);
                    $pdf->Write(10, $data['nature_locaux_status_autre_valeur']); 
                }
                
                if (isset($data['travaux']) && is_array($data['travaux'])) {
                    foreach ($data['travaux'] as $travaux) {
                        if ($travaux == "travaux_1") {
                            $pdf->SetXY(19.5, 145);
                            $pdf->Write(10, 'X'); 
                        }
                        else if ($travaux == "travaux_2") {
                            $pdf->SetXY(19.5, 152.5);
                            $pdf->Write(10, 'X'); 
                            
                            if (isset($data['travaux_2_details']) && is_array($data['travaux_2_details'])) {
                                foreach ($data['travaux_2_details'] as $travaux_2_details) {
                                    if ($travaux_2_details == "travaux_2_details_1") {
                                        $pdf->SetXY(96, 156.2);
                                        $pdf->Write(10, 'X'); 
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_2") {
                                        $pdf->SetXY(33.2, 160);
                                        $pdf->Write(10, 'X'); 
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_3") {
                                        $pdf->SetXY(66.7, 160);
                                        $pdf->Write(10, 'X'); 
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_4") {
                                        $pdf->SetXY(97.3, 160);
                                        $pdf->Write(10, 'X'); 
                                    }
                                    else if ($travaux_2_details == "travaux_2_details_5") {
                                        $pdf->SetXY(154, 160);
                                        $pdf->Write(10, 'X'); 
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_6") {
                                        $pdf->SetXY(19.5, 163.5);
                                        $pdf->Write(10, 'X'); 
                                    }    

                                }
                            }
                        }
                        else if ($travaux == "travaux_3") {
                            $pdf->SetXY(19.5, 170.8);
                            $pdf->Write(10, 'X'); 

                        }
                        else if ($travaux == "travaux_4") {
                            $pdf->SetXY(19.5, 174.3);
                            $pdf->Write(10, 'X'); 
                        }
                        else if ($travaux == "travaux_5") {
                            $pdf->SetXY(19.5, 178);
                            $pdf->Write(10, 'X'); 
                        }
                        else if ($travaux == "travaux_6") {
                            $pdf->SetXY(19.5, 192.4);
                            $pdf->Write(10, 'X'); 

                        }
                    }
                }

                
                $pdf->SetXY(93, 256);
                $pdf->Write(10, $data['fait_a']); 

                $pdf->SetXY(130, 256);
                $fait_le = \DateTime::createFromFormat('Y-m-d', $data['fait_le']);
                $fait_le = $fait_le ? $fait_le->format('d/m/Y') : '';
                $pdf->Write(10, $fait_le); 

                $signatureBase64 = $data['signature'];
                $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
                $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature.png');
                file_put_contents($signaturePath, $signatureData);
                $pdf->Image($signaturePath, 150, 260, 52, 16);


            }

        }

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        return response()->file($outputPath, [
            'Content-Disposition' => 'inline; filename="' . $uid . '"'
        ]);
    }

    /**
     * @brief Génère un bulletin d'intervention (BI) détaillé avec gestion multi-plateforme.
     *
     * Cette méthode produit des bulletins d'intervention complets incluant :
     * - Logo personnalisé de l'entreprise cliente.
     * - Informations complètes sur l'intervention et les intervenants.
     * - Gestion des photos avant/après avec redimensionnement automatique.
     * - Cases à cocher pour le statut de l'intervention.
     * - Compléments d'information avec textes et images.
     * - Signature numérique avec adaptation selon la plateforme (Android/autres).
     * - Page supplémentaire pour les compléments clients si nécessaire.
     *
     * @param \Illuminate\Http\Request $request Requête GET avec paramètres :
     *                                         - uid : Identifiant unique du bulletin
     *                                         - document : Type de document (BI)
     *                                         - client : Identifiant du client
     *                                         - isAndroid : "1" si généré depuis Android, autre sinon
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     *         Fichier PDF du bulletin en affichage inline ou erreur JSON.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException Si le fichier JSON de données n'existe pas.
     * @throws \RuntimeException Si une erreur survient lors du traitement des images.
     *
     * @note Les images sont automatiquement redimensionnées tout en conservant leur ratio d'aspect.
     * @note La signature est adaptée selon la plateforme pour un rendu optimal.
     * @note Une page supplémentaire est créée automatiquement si des compléments clients existent.
     * @warning Les chemins d'images doivent être valides et les fichiers accessibles dans le storage.
     * @example GET /generateBi?uid=12345&document=BI&client=CLIENT001&isAndroid=0
     */
    public function generateBi(Request $request)
    {
        $uid = $request->query('uid');
        $document = $request->query('document');
        $client = $request->query('client');
        $isAndroid = $request->query('isAndroid');

        Log::info("Test : " . $isAndroid);

        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // PDF d'origine
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // Lire le fichier JSON
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

         // Remplissage des champs avec des valeurs dynamiques
        $data = json_decode(file_get_contents($jsonPath), true);

        // Initialiser FPDI
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        // Affichage logo de l'entreprise / client en haut de la page
        $logoPath = storage_path('app/public/'.$client.'/logo.png');

        if ($logoPath && file_exists($logoPath)) {

            $pdf->Image($logoPath, 10, 12, 118, 25, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            
        }

        
        $pdf->SetFont('helvetica', 'b', 8);
        $pdf->SetXY(23, 36.70);
        $pdf->Write(10, ($data['uid'] ?? 'TEST'));    
        $pdf->SetXY(31, y: 44);
        $pdf->Write(10, ($data['date_intervention'] ?? date('d/m/Y')));    
        $pdf->SetXY(32, 54);
        $pdf->Write(10, ($data['intervenant'] ?? ''));    
        $pdf->SetXY(29, 59.5);
        $pdf->Write(10, ($data['equipier'] ?? ''));    

        
        $pdf->SetXY(37.25, 66);
        $pdf->Write(10, ($data['code_client'] ?? ''));  
        $pdf->SetXY(29, 71);
        $pdf->Write(10, ($data['email_client'] ?? ''));  
        $pdf->SetXY(142, 66.25);
        $pdf->Write(10, ($data['telephone_client'] ?? ''));  
        $pdf->SetXY(139, 71.5);
        $pdf->Write(10, ($data['portable_client'] ?? ''));  
        
        $pdf->SetFont('helvetica', 'b', 11);

        $pdf->SetXY(15, 88);
        $pdf->MultiCell(180, 10, ($data['description']."\n" ?? ''));

        $pdf->SetFont('helvetica', '', 9);

        $pdf->SetXY(135, 45);
        $pdf->Write(10, ($data['nom_client'] ?? ''));    
        $pdf->SetXY(135, 49);
        $pdf->Write(10, ($data['adresse_facturation'] ?? ''));  
        $pdf->SetXY(135, 53);
        $pdf->Write(10, $data['cp_facturation'] . ' ' . $data['ville_facturation']);

        

        $pdf->SetFont('helvetica', '', 9);

        $pdf->SetXY(70, 45);
        $pdf->Write(10, ($data['lieu_intervention'] ?? ''));    
        $pdf->SetXY(70, 50);
        $pdf->Write(10, ($data['adresse_intervention'] ?? ''));  
        $pdf->SetXY(70, 55);
        $pdf->Write(10, $data['cp_intervention'] . ' ' . $data['ville_intervention']);

        $pdf->SetFont('helvetica', '', 9);

        $pdf->SetXY(15, 108);
        $pdf->MultiCell(180, 10, ($data['compte_rendu']."\n" ?? ''));

        $pdf->SetXY(88, 194);
        $pdf->MultiCell(70, 10, ($data['materiel']."\n" ?? ''));

        $pdf->SetFont('helvetica', '', 11);
        if (isset($data['intervention_realisable']) && ($data['intervention_realisable'] == 'oui')) {            
            $pdf->SetXY(14.6, 127.6);
            $pdf->Write(10, 'X');
        }        
        if (isset($data['terminee']) && ($data['terminee'] == 'oui')) {            
            $pdf->SetXY(14.6, 132.5);
            $pdf->Write(10, 'X');
        }
        if (isset($data['intervention_suite']) && ($data['intervention_suite'] == 'oui')) {            
            $pdf->SetXY(14.6, 137.3);
            $pdf->Write(10, 'X');
        }
        if (isset($data['facturable']) && ($data['facturable'] == 'oui')) {            
            $pdf->SetXY(14.6, 142);
            $pdf->Write(10, 'X');
        }
        if (isset($data['devis_a_faire']) && ($data['devis_a_faire'] == 'oui')) {            
            $pdf->SetXY(14.6, 146.8);
            $pdf->Write(10, 'X');
        }

        if (isset($data['absent']) && ($data['absent'] == 'oui')) {            
            $pdf->SetXY(14.6, 160); 
            $pdf->Write(10, 'X');
        }

        $pdf->SetFont('helvetica', '', 9);

        if (isset($data['photo_avant'])) {
            $imagePath = storage_path('app/public/'.$data['photo_avant']);
            if ($imagePath && file_exists($imagePath)) {
                list($width, $height) = getimagesize($imagePath); // Récupère la taille originale
                $maxWidth = 62;
                $maxHeight = 50;
                // Calcul du redimensionnement en conservant le ratio
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                // Calcul des positions pour centrer dans le carré
                $x = 68.5 + ($maxWidth - $newWidth) / 2;
                $y = 134 + ($maxHeight - $newHeight) / 2;

                // Affichage de l'image redimensionnée
                $pdf->Image($imagePath, $x, $y, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);

            }
        }

        if (isset($data['photo_apres'])) {
            $imagePath = storage_path('app/public/'.$data['photo_apres']);
            if ($imagePath && file_exists($imagePath)) {
                list($width, $height) = getimagesize($imagePath); // Récupère la taille originale
                $maxWidth = 62;
                $maxHeight = 50;
                // Calcul du redimensionnement en conservant le ratio
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                // Calcul des positions pour centrer dans le carré
                $x = 139 + ($maxWidth - $newWidth) / 2;
                $y = 134 + ($maxHeight - $newHeight) / 2;

                // Affichage de l'image redimensionnée
                $pdf->Image($imagePath, $x, $y, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);

            }
        }

        //Gestion des infos complémentaires
        $countComplement = 0;
        $x = 88;
        $y = 194;
        if (isset($data['complements']) && count($data['complements']) > 0) {
            foreach ($data['complements'] as $complement) {
                $countComplement = $countComplement + 1;
                if ($countComplement <= 2) {
                    // Affichage des infos complémentaires
                    if (isset($complement['comment'])) {
                        $pdf->setXY($x,$y + 71.5);
                        $pdf->MultiCell(55,10,$complement['comment']. "\n");   
                    }
                    if (isset($complement['image'])) {
                        $imagePath = storage_path('app/public/'.$complement['image']);
                        if ($imagePath && file_exists($imagePath)) {
                            list($width, $height) = getimagesize($imagePath); // Récupère la taille originale
                            $maxWidth = 55;
                            $maxHeight = 50;
                            // Calcul du redimensionnement en conservant le ratio
                            if ($width > $height) {
                                $newWidth = $maxWidth;
                                $newHeight = ($height / $width) * $maxWidth;
                            } else {
                                $newHeight = $maxHeight;
                                $newWidth = ($width / $height) * $maxHeight;
                            }
                            // Calcul des positions pour centrer dans le carré
                            $xImage = $x + ($maxWidth - $newWidth) / 2;
                            $yImage = $y + 28.2 + ($maxHeight - $newHeight) / 2;

                            // Affichage de l'image redimensionnée
                            $pdf->Image($imagePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);    
                        }
                    }
                    $x = $x + 58.5;
                }
            }
        }

        if (isset($data['signature'])) {
            $signatureBase64 = $data['signature'];
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
            $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature.png');
            file_put_contents($signaturePath, $signatureData);
            
            list($width, $height) = getimagesize($signaturePath); // Récupère la taille 

            $maxWidth = 60;
            $maxHeight = 18;

            // Calcul du redimensionnement en conservant le ratio
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = ($height / $width) * $maxWidth;
            } else {
                $newHeight = $maxHeight;
                $newWidth = ($width / $height) * $maxHeight;
            }
            // Calcul des positions pour centrer dans le carré
            $xImage = 14 + ($maxWidth - $newWidth) ;
            $yImage = 197 + ($maxHeight - $newHeight);

            if ($isAndroid === "1") {
                $newWidth /= 1.5;
                $newHeight /= 1.5;

                $yImage += 10;
            }
            else {
                $newWidth /= 1.3;
                $newHeight /= 1.3;
            }
            
            $pdf->Image($signaturePath, $xImage + 2, $yImage + 3, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);    

                                  
        }

        $pdf->SetFont('helvetica', 'i', 12);
        $pdf->SetXY( 57, 213);
        $pdf->Write(12, $data['fait-le']);

        $pdf->SetFont('helvetica', 'i', 14);
        $pdf->SetXY( 15, 232);
        $pdf->Write(12, $data['intervenant']);


        $x_complement_client = 10; //alignement x des complement client 
        $y_complement_client = 20; //alignement y des complement client 

        if ($data['complement_client'] && count($data['complement_client']) > 0) {

            $pdf->AddPage(); 
            $pdf->SetFont('helvetica', 'b', 9);

            // boucle sur les complement client 
            foreach ($data['complement_client'] as $item) { // Donne a $item un tableau avec item et question
                
                if ($item['type'] === 'text') {
                    $pdf->Text($x_complement_client, $y_complement_client, $item['question']);
                    $pdf->Text($x_complement_client + 80, $y_complement_client, ':');

                    $pdf->SetXY( $x_complement_client + 85, $y_complement_client);
                    $pdf->MultiCell(100,10,$item['value'] . "\n");  
                    
                }
                else  {
                    $pdf->Text($x_complement_client, $y_complement_client, $item['question']);
                    $pdf->Text($x_complement_client + 80, $y_complement_client, ':');

                    $imagePath = storage_path('app/public/'.$item['value']);
                    if ($imagePath && file_exists($imagePath)) {
                        list($width, $height) = getimagesize($imagePath); // Récupère la taille originale
                        $maxWidth = 55;
                        $maxHeight = 50;
                        // Calcul du redimensionnement en conservant le ratio
                        if ($width > $height) {
                            $newWidth = $maxWidth;
                            $newHeight = ($height / $width) * $maxWidth;
                        } else {
                            $newHeight = $maxHeight;
                            $newWidth = ($width / $height) * $maxHeight;
                        }
                        // Calcul des positions pour centrer dans le carré
                        $xImage = $x_complement_client + ($maxWidth - $newWidth) / 2;
                        $yImage = $y_complement_client + 5 + ($maxHeight - $newHeight) / 2;

                        // Affichage de l'image redimensionnée
                        $pdf->Image($imagePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);  
                        
                        $y_complement_client = $yImage + $newHeight + 8;
                    }
                }

                $y_complement_client += 8;
                
            }

            //$pdf->Text($x_complement_client, $y_complement_client, 'Test');

        }

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        return response()->file($outputPath, [
            'Content-Disposition' => 'inline; filename="' . $uid . '"'
        ]);
    }

    /**
     * @brief Formate un texte long en respectant les contraintes de longueur par ligne.
     *
     * Cette méthode utilitaire découpe intelligemment les textes longs :
     * - Première ligne limitée à 52 caractères.
     * - Lignes suivantes limitées à 108 caractères chacune.
     * - Préservation de l'intégrité du texte sans coupure de mots.
     *
     * @param string $texte Le texte à formater et découper.
     *
     * @return array Tableau contenant les lignes formatées prêtes pour l'affichage PDF.
     *
     * @note Méthode privée utilisée pour optimiser l'affichage des textes longs dans les PDF.
     * @note Les limites de caractères sont calibrées pour l'affichage optimal dans les modèles PDF.
     * @example formatTexte("Un très long texte...") retourne ["Un très long...", "...suite du texte"]
     */
    private function formatTexte($texte) {
        $texte = trim($texte);
        $resultat = [];
    
        // Première ligne : max 40 caractères
        if (strlen($texte) <= 52) {
            $resultat[] = $texte;
        } else {
            $resultat[] = substr($texte, 0, 52);
            $reste = substr($texte, offset: 50);
    
            // Lignes suivantes : max 100 caractères
            while (strlen($reste) > 0) {
                $resultat[] = substr($reste, 0, 108);
                $reste = substr($reste, 108);
            }
        }
    
        // Retourne le texte avec des retours à la ligne
        return $resultat;
    }
    

    /**
     * @brief Génère un formulaire CERFA 15497-03 pour les interventions sur équipements frigorifiques.
     *
     * Cette méthode spécialisée traite les formulaires CERFA complexes avec :
     * - Remplissage automatique des champs d'identification (opérateur, détenteur).
     * - Gestion des types d'intervention (assemblage, maintenance, contrôle...).
     * - Traitement des différents types de fluides frigorigènes (HCFC, HFC, HFO).
     * - Gestion des périodicités de contrôle selon la réglementation.
     * - Traitement des fuites détectées avec localisation et réparations.
     * - Calcul des quantités de fluides chargés et récupérés.
     * - Double signature numérique (opérateur et détenteur).
     * - Formatage automatique des dates.
     *
     * @param \Illuminate\Http\Request $request Requête GET avec paramètres :
     *                                         - uid : Identifiant unique du formulaire
     *                                         - document : Type de document (cerfa_15497-03)
     *                                         - client : Identifiant du client
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     *         Fichier PDF CERFA complété en affichage inline ou erreur JSON.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException Si le fichier JSON de données n'existe pas.
     * @throws \InvalidArgumentException Si le format de date est incorrect.
     *
     * @note Conforme à la réglementation française sur les fluides frigorigènes.
     * @note Les coordonnées de positionnement sont précisément calibrées pour le formulaire CERFA officiel.
     * @note Deux signatures distinctes sont gérées : opérateur et détenteur d'équipement.
     * @warning Les données doivent respecter le format attendu par l'administration française.
     * @see https://www.service-public.fr/professionnels-entreprises/vosdroits/R14311 Documentation officielle CERFA.
     */
    public function generateCerfa(Request $request)
    {
        $uid = $request->query('uid');
        $document = $request->query('document');
        $client = $request->query('client');
        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // PDF d'origine
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // Lire le fichier JSON
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

         // Remplissage des champs avec des valeurs dynamiques
        $data = json_decode(file_get_contents($jsonPath), true);

        // Initialiser FPDI
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        
        // Définir la police et la taille du texte
        $pdf->SetFont('helvetica', '', 10);

        $pdf->SetXY(15, 32);
        $pdf->Write(10, ($uid ?? ''));

        // Définir la police et la taille du texte
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0); // Texte noir

        // Écrire les données dans le PDF (adapter les positions X et Y)
        $largeur = 60;
        $pdf->SetXY(45, 32.5);
        $operateur = ($data['operateur'] ?? "") . "\n";
        $pdf->MultiCell($largeur, 10, $operateur);

        $pdf->SetXY(125, 35);
        $detenteur = ($data['detenteur'] ?? "") . "\n";
        $pdf->MultiCell($largeur, 10, $detenteur);

        $pdf->SetXY(75, 47);
        $pdf->Write(10, ($data['numero_attestation_capacite'] ?? ''));

        $pdf->SetXY(45, 60);
        $pdf->Write(10, ($data['identification'] ?? ''));

        $pdf->SetXY(172, 53);
        $pdf->Write(10, ($data['denomination'] ?? ''));

        $pdf->SetXY(170, 58);
        $pdf->Write(10, ($data['charge'] ?? ''));

        $pdf->SetXY(170, 63);
        $pdf->Write(10, ($data['tonnage'] ?? ''));

        if ($data['nature_intervention'] == "assemblage") {
            $pdf->SetXY(54, 68);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "mise_service") {
            $pdf->SetXY(54, 73);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "modification") {
            $pdf->SetXY(54, 78);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "maintenance") {
            $pdf->SetXY(54, 83);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "controle_periodique") {
            $pdf->SetXY(116, 68);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "controle_non_periodique") {
            $pdf->SetXY(116, 73);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "dementelement") {
            $pdf->SetXY(116, 78);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "autre") {
            $pdf->SetXY(116, 83);
            $pdf->Write(10, 'X');    
        }

        $pdf->SetXY(153, 83);
        $pdf->Write(10, ($data['autre_valeur'] ?? ''));

        $pdf->SetXY(75, 94);
        $pdf->Write(10, ($data['identification_controle'] ?? ''));

        list($year,$month,$day) = explode('-', $data['date_controle']);
        $pdf->SetXY(153, 94);
        $pdf->Write(10, ($day ?? ''));
        $pdf->SetXY(165, 94);
        $pdf->Write(10, ($month ?? ''));
        $pdf->SetXY(177, 94);
        $pdf->Write(10, ($year ?? ''));

        
        $pdf->SetFont('dejavusans', '', 9);
        if ($data['detection_fuites'] == 'non') {
            $pdf->SetXY(148.8, 100.7);
        }
        else {
            $pdf->SetXY(116.7, 100.7);
        }
        $pdf->Write(10, '●');
        
        $pdf->SetFont('helvetica', '', 8);    


        if ($data['hcfc'] == '2-30') {
            $x = 107;   
            $y = 109.5;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');      
        }
        elseif ($data['hcfc'] == '30-300') { 
            $x = 138;  
            $y = 109.5;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');     
        }
        elseif ($data['hcfc'] == '300') {
            $x = 169.5;  
            $y = 109.5;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');       
        }    

        if ($data['hfc_pfc'] == '5-50') {
            $x = 107;     
            $y = 114;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');  
        }
        elseif ($data['hfc_pfc'] == '50-500') {
            $x = 138;  
            $y = 114;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');     
        }
        elseif ($data['hfc_pfc'] == '500') {
            $x = 169.5;
            $y = 114;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');  
        }  

        if ($data['hfo'] == '1-10') {
            $x = 107;     
            $y = 119;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');  
        }
        elseif ($data['hfo'] == '10-100') {
            $x = 138;  
            $y = 119;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');     
        }
        elseif ($data['hfo'] == '100') {
            $x = 169.5;
            $y = 119;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');  
        }  

        if ($data['equipement_sans_detection'] == 'sans12') {
            $x = 107;   
            $y = 123;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['equipement_sans_detection'] == 'sans6') { 
            $x = 138;  
            $y = 123;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['equipement_sans_detection'] == 'sans3') {
            $x = 169.5;
            $y = 123;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        } 

        if ($data['equipement_avec_detection'] == 'avec24') {
            $x = 107;   
            $y = 128;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['equipement_avec_detection'] == 'avec12') { 
            $x = 138;  
            $y = 128;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['equipement_avec_detection'] == 'avec3') {
            $x = 169.5;
            $y = 128;
            $pdf->SetXY($x, $y);
            $pdf->Write(10, 'X');   
        }
        
        if ($data['constat_fuites'] == 'oui') {
            $pdf->SetXY(22.5, 149.5);
            $pdf->Write(10, 'X');

            // gestion de la localisation des fuites
            $largeur = 120;
            $pdf->SetXY(60, 139.5);
            $pdf->MultiCell($largeur, 10, ($data['localisation_fuite_1']."\n" ?? ''));
            if ($data['reparation_fuite_1'] == 'reparation_fuite_1_fait') {
                $pdf->SetXY(173.5, 136);
                $pdf->Write(10, 'X');   
            }
            elseif ($data['reparation_fuite_1'] == 'reparation_fuite_1_A_Faire') { 
                $pdf->SetXY(173.5, 140);
                $pdf->Write(10, 'X');   
            }
            
            $pdf->SetXY(60, 147.5);
            $pdf->MultiCell($largeur, 10, ($data['localisation_fuite_2']."\n" ?? ''));
            if ($data['reparation_fuite_2'] == 'reparation_fuite_2_fait') {
                $pdf->SetXY(173.5, 144);
                $pdf->Write(10, 'X');   
            }
            elseif ($data['reparation_fuite_2'] == 'reparation_fuite_2_A_Faire') { 
                $pdf->SetXY(173.5, 148);
                $pdf->Write(10, 'X');   
            }
            
            $pdf->SetXY(60, 155.5);
            $pdf->MultiCell($largeur, 10, ($data['localisation_fuite_3']."\n" ?? ''));
            if ($data['reparation_fuite_3'] == 'reparation_fuite_3_fait') {
                $pdf->SetXY(173.5, 152);
                $pdf->Write(10, 'X');   
            }
            elseif ($data['reparation_fuite_3'] == 'reparation_fuite_3_A_Faire') { 
                $pdf->SetXY(173.5, 156);
                $pdf->Write(10, 'X');   
            }
        }
        else {
            $pdf->SetXY(22.5, 151.5);
            $pdf->Write(10, 'X');
        }

        $pdf->SetXY(90, 165);
        $pdf->Write(10, $data['quantite_chargee_totale']);
        $pdf->SetXY(90, 169.5);
        $pdf->Write(10, $data['quantite_chargee_A']);
        $pdf->SetXY(75, 174.2);
        $pdf->Write(10, $data['fluide_A']);
        $pdf->SetXY(90, 179);
        $pdf->Write(10, $data['quantite_chargee_B']);
        $pdf->SetXY(90, 184);
        $pdf->Write(10, $data['quantite_chargee_C']);

        $pdf->SetXY(184, 165);
        $pdf->Write(10, $data['quantite_recuperee_totale']);
        $pdf->SetXY(184, 169.5);
        $pdf->Write(10, $data['quantite_recuperee_D']);
        $pdf->SetXY(170, 174.2);
        $pdf->Write(10, $data['BSFF']);
        $pdf->SetXY(184, 179);
        $pdf->Write(10, $data['quantite_recuperee_E']);
        $pdf->SetXY(155, 184);
        $pdf->Write(10, $data['identification_E']);

        if ($data['fluide_non_inflammable'] == 'UN1078') {
            $pdf->SetXY(13.5, 197.5);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['fluide_non_inflammable'] == 'autre_cas_non_inflammable') { 
            $pdf->SetXY(108, 197.5);
            $pdf->Write(10, 'X');   
            
            $pdf->SetXY(166 , 197.4);
            $pdf->Write(10, $data['autre_fluide_non_inflammable']);   
        }

        if ($data['fluide_inflammable'] == 'UN3161') {
            $pdf->SetXY(13.5, 207);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['fluide_inflammable'] == 'autre_cas_inflammable') { 
            $pdf->SetXY(108, 207);
            $pdf->Write(10, 'X');   
            
            $pdf->SetXY(160, 207);
            $pdf->Write(10, $data['autre_fluide_inflammable']);   
        }
       
        
        $largeur = 180;
        $pdf->SetXY(13, 218.5);
        $pdf->MultiCell($largeur, 10, ($data['installation_destination_fluide']."\n" ?? ''));

        $pdf->SetXY(13, 232.5);
        $pdf->MultiCell($largeur, 10, ($data['observations']."\n" ?? '')); 
        

        //Gestion des signatures
        $pdf->SetXY(45, 258);
        $pdf->Write(10, ($data['nom_signataire_operateur'] ?? ''));   
        $pdf->SetXY(45, 264.5);
        $pdf->Write(10, ($data['qualite_signataire_operateur'] ?? ''));  
        $pdf->SetXY(45, 274);
        $pdf->Write(10, ($data['date_signature_operateur'] ?? ''));   

        $signatureBase64 = $data['signature-operateur'];
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
        $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature_operateur.png');
        file_put_contents($signaturePath, $signatureData);
        $pdf->Image($signaturePath, 65, 274, 32, 8);
        
        $pdf->SetXY(125, 258);
        $pdf->Write(10, ($data['nom_signataire_detenteur'] ?? ''));   
        $pdf->SetXY(125, 264.5);
        $pdf->Write(10, ($data['qualite_signataire_detenteur'] ?? ''));  
        $pdf->SetXY(125, 274);
        $pdf->Write(10, ($data['date_signature_detenteur'] ?? ''));  

        $signatureBase64 = $data['signature-detenteur'];
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
        $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature_detenteur.png');
        file_put_contents($signaturePath, $signatureData);
        $pdf->Image($signaturePath, 145, 274, 32, 8);

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        return response()->file($outputPath, [
            'Content-Disposition' => 'inline; filename="' . $uid . '"'
        ]);
    }


    /**
     * @brief Upload et traitement sécurisé de fichiers PDF pour les devis certifiés.
     *
     * Cette méthode gère l'upload de fichiers PDF avec validation et organisation :
     * - Validation stricte du format PDF uniquement.
     * - Extraction et vérification du token depuis le nom de fichier.
     * - Vérification de l'existence du token en base de données.
     * - Organisation automatique des fichiers par organisation et devis.
     * - Création automatique de l'arborescence de stockage.
     * - Logging détaillé de toutes les opérations d'upload.
     *
     * @param Request $request Requête POST avec fichier :
     *                        - pdf_file : Fichier PDF à uploader (obligatoire)
     *                        Le nom du fichier doit contenir le token valide.
     *
     * @return mixed Réponse JSON avec :
     *              - Succès : message, path, original_filename
     *              - Erreur : message d'erreur et code HTTP approprié
     *
     * @throws Exception Si le fichier n'est pas un PDF valide.
     * @throws Exception Si le token n'existe pas en base.
     * @throws Exception Si la création du répertoire ou le déplacement du fichier échoue.
     *
     * @note L'arborescence créée suit le pattern : {organisation_id}/devis/{devis_id}_{token}/
     * @note Tous les uploads sont tracés dans les logs pour audit et sécurité.
     * @warning Seuls les fichiers PDF sont acceptés pour des raisons de sécurité.
     * @par Exemple:
     * POST /upload avec un fichier "ABC123.pdf" crée le chemin ORG001/devis/DEV456_ABC123/DEV456_ABC123.pdf
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf',
        ]);

        if (!$request->hasFile('pdf_file')) {
            return response()->json(['message' => 'Aucun fichier reçu'], 400);
        }

        $file = $request->file('pdf_file');
        $originalFilename = $file->getClientOriginalName();
        $noToken = pathinfo($originalFilename, PATHINFO_FILENAME);

        $token = Token::where('token', $noToken)->first();

        if (!$token) {
            return response()->json(['message' => 'Token non trouvé'], 404);
        }

        $name = $token->devis_id . '_' . $noToken;
        $newFilename = $name . '.pdf';

        $relativePath = $token->organisation_id . '/devis/' . $name . '/';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $file->move($fullPath, $newFilename);

        $finalPath = $relativePath . '/' . $newFilename;
        
        \Log::info("Fichier PDF uploadé avec succès", [
            'token' => $noToken,
            'devis_id' => $token->devis_id,
            'nom_fichier' => $newFilename,
            'organisation' => $token->organisation_id,
            'chemin_complet' => $finalPath
        ]);

        return response()->json([
            'message' => 'Upload réussi', 
            'path' => $finalPath,
            'original_filename' => $originalFilename
        ], 200);
    }

    /**
     * @brief Affiche la vue d'un devis PDF avec vérification du statut de certification.
     *
     * Cette méthode de consultation sécurisée :
     * - Vérifie l'existence et la validité du token de devis.
     * - Détermine si une version certifiée du devis existe.
     * - Prépare les données nécessaires pour l'affichage dans la vue.
     * - Gère les accès non autorisés avec des erreurs 404 appropriées.
     *
     * @param Request $request Requête HTTP (généralement GET).
     * @param string $token Token unique d'identification du devis à consulter.
     *
     * @return mixed Vue 'devis_pdf' avec les données du devis ou erreur 404.
     *
     * @throws Exception Si le token n'existe pas en base.
     *
     * @note La vue reçoit : client, token, nomDevis, isCertified pour l'affichage conditionnel.
     * @note La vérification de certification recherche un fichier avec suffixe '_certifie.pdf'.
     * @warning Aucune vérification d'autorisation supplémentaire n'est effectuée au-delà de l'existence du token.
     * @par Exemple:
     * GET /devis/ABC123 affiche le devis associé au token "ABC123" avec son statut de certification.
     */
    public function viewDevis(Request $request, $token){
        try {
            Log::info("Tentative d'accès au devis", [
                'token_recu' => $token,
                'ip_client' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validation du token
            if (empty($token) || !is_string($token)) {
                Log::error("Token invalide", ['token' => $token]);
                abort(404, "Token invalide");
            }

            $leToken = Token::where('token', $token)->first();

            if (!$leToken) {
                Log::warning("Token non trouvé en base de données", [
                    'token_recherche' => $token,
                    'tokens_existants_sample' => Token::limit(5)->pluck('token')->toArray()
                ]);
                abort(404, "Devis non trouvé");
            }

            Log::info("Token trouvé en base", [
                'token' => $token,
                'organisation_id' => $leToken->organisation_id,
                'devis_id' => $leToken->devis_id,
                'created_at' => $leToken->created_at
            ]);

            // Validation des données du token
            if (empty($leToken->organisation_id) || empty($leToken->devis_id)) {
                Log::error("Données token incomplètes", [
                    'token' => $token,
                    'organisation_id' => $leToken->organisation_id,
                    'devis_id' => $leToken->devis_id
                ]);
                abort(404, "Données devis incomplètes");
            }

            $devisName = $leToken->devis_id . '_' . $token;
            $certifiedPath = $leToken->organisation_id . '/devis/' . $devisName . '/' . $devisName . '_certifie.pdf';
            $normalPath = $leToken->organisation_id . '/devis/' . $devisName . '/' . $devisName . '.pdf';
            
            $isCertified = Storage::disk('public')->exists($certifiedPath);
            $hasNormalFile = Storage::disk('public')->exists($normalPath);

            Log::info("Vérification des fichiers", [
                'devis_name' => $devisName,
                'chemin_certifie' => $certifiedPath,
                'fichier_certifie_existe' => $isCertified,
                'chemin_normal' => $normalPath,
                'fichier_normal_existe' => $hasNormalFile,
                'storage_public_path' => storage_path('app/public/')
            ]);

            // Vérification qu'au moins un fichier existe
            if (!$isCertified && !$hasNormalFile) {
                Log::error("Aucun fichier PDF trouvé", [
                    'token' => $token,
                    'devis_name' => $devisName,
                    'chemin_certifie' => $certifiedPath,
                    'chemin_normal' => $normalPath
                ]);
                abort(404, "Fichier PDF non trouvé");
            }

            // Vérification supplémentaire de l'existence du répertoire de base
            $baseDevisPath = $leToken->organisation_id . '/devis/' . $devisName;
            $baseExists = Storage::disk('public')->exists($baseDevisPath);
            
            Log::info("Vérification du répertoire de base", [
                'chemin_base' => $baseDevisPath,
                'repertoire_existe' => $baseExists,
                'contenu_repertoire' => $baseExists ? Storage::disk('public')->files($baseDevisPath) : []
            ]);

            // Préparation des données pour la vue avec validation
            $viewData = [
                'client' => $leToken->organisation_id,
                'token' => $token,
                'nomDevis' => $leToken->devis_id,
                'isCertified' => $isCertified
            ];

            Log::info("Données préparées pour la vue", $viewData);

            return view('devis_pdf', $viewData);

        } catch (\Exception $e) {
            Log::info("Erreur dans viewDevis", [
                'token' => $token ?? 'non défini',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Si c'est une erreur HTTP (abort), on la relance
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }

            // Sinon, on retourne une erreur 500 avec un message générique
            abort(500, "Erreur interne du serveur lors de l'accès au devis");
        }
    }
}
