<?php

namespace App\Http\Controllers;

use App\Models\TokenLinks;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response;


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
     * Formate et normalise le texte pour l'affichage dans les PDF
     * - Supprime les retours à la ligne et espaces superflus
     * - Capitalise la première lettre et après les points
     * - Normalise la ponctuation
     * 
     * @param string $texte Le texte à reformater
     * @return string Le texte normalisé
     */
    public function reformaterTexte($texte) {
           // Normalise les retours Windows (\r\n -> \n)
        $texte = str_replace("\r\n", "\n", $texte);

        // Supprime espaces en fin/début de ligne
        $texte = preg_replace('/[ \t]+$/m', '', $texte);
        $texte = preg_replace('/^[ \t]+/m', '', $texte);

        

        // Supprime les retours à la ligne
        $texte = str_replace("\n", ' ', $texte);

        // Supprime les espaces multiples (remplace par un seul)
        $texte = preg_replace('/\s+/u', ' ', $texte);

        // Supprime espace avant ponctuation
        $texte = preg_replace('/\s+([.,;!?])/u', '$1', $texte);

        // Assure un seul espace après ponctuation
        $texte = preg_replace('/([.,;!?])\s*/u', '$1 ', $texte);

        // Met une majuscule au début et après un point
        $texte = preg_replace_callback('/(^\p{L}|(?<=\.\s)\p{L})/u', function($matches) {
            return mb_strtoupper($matches[0], 'UTF-8');
        }, $texte);
        

        return trim($texte);
    }


    /**
     * Convertit une date dans n'importe quel format en format français (d/m/Y)
     * - Accepte les formats avec '/', '-', ou '.'
     * - Gère les erreurs de parsing gracieusement
     * 
     * @param string $date La date à formater
     * @return string|null La date au format d/m/Y ou null si invalide
     */
    public function formatDate($date): ?string 
    {
        // Si la date est vide ou invalide, retourner null
        if (empty($date)) {
            return null;
        }

        $date = trim($date, " ");

        $date = trim($date);
        // Normalise les séparateurs en "/"
        $date = str_replace(['/', '-', '.'], '/', $date);

        try {
            // Parse la date et la formate en français
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            // Log l'erreur et retourne null en cas d'échec
            Log::error("Format de date invalide : " . $date);
            return null;
        }
    }


    /**
     * @brief Affiche un document PDF à partir d'un token d'accès.
     *
     * Cette méthode :
     * - Recherche le fichier PDF correspondant au token fourni dans TokenLinks.
     * - Vérifie l'existence du fichier JSON associé contenant les métadonnées.
     * - Extrait les informations du client, document et UID.
     * - Retourne la vue d'affichage PDF avec les données nécessaires.
     *
     * @param string $token Jeton unique associé au document PDF pour l'identification sécurisée.
     *
     * @return mixed Vue d'affichage du PDF ou erreur 404.
     *
     * @throws \Exception Si aucun token valide n'est trouvé dans la base.
     * @throws \Exception Si le fichier JSON associé n'existe pas.
     *
     * @note Cette méthode est utilisée pour l'affichage dans le navigateur, pas pour le téléchargement direct.
     * @see TokenLinks Pour la gestion des tokens et leurs liens.
     * @par Exemple:
     * GET /pdf/show/abc123 pour afficher le PDF associé au token "abc123".
     */
    public function show($token)
    {
        // Récupère les données du token depuis la base de données
        $dataToken = TokenLinks::where('token', $token)->get()->first();

        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );
        
        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];
        

        // Retourne la vue PDF avec les données pour affichage
        return view('pdf', compact('client', 'document', 'uid', 'token'));
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
     * @throws \Exception Si une erreur survient lors de la création du fichier JSON.
     * @throws \Exception Si le modèle PDF source est introuvable.
     *
     * @note Le PDF généré est "aplati" pour empêcher les modifications ultérieures.
     * @warning Cette méthode nécessite que les modèles PDF soient présents dans storage/app/public/.
     * @par Exemple:
     * POST avec JSON : {"uid": "12345", "document": "1301-SD", "nom": "Dupont", ...}
     */
    public function generateDownloadPDF(Request $request)
    {
        // Extrait les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        $uid = $data['uid'];
        $document = $data['document'];
        
        // === ÉTAPE 1 : CRÉATION DU DOSSIER DE STOCKAGE ===
        $folderPath = storage_path('app/public/' . $document . '/' . $uid);

        // Crée le dossier s'il n'existe pas
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

        // === ÉTAPE 2 : SAUVEGARDE DES DONNÉES EN JSON ===
        $jsonPath = $folderPath . '/'. $uid .'.json';
        try {
            // Supprime l'ancien JSON s'il existe
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            
            // Structure les données selon le type de document
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

            // Écrit les données JSON dans le fichier
            file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            // Log l'erreur en cas d'échec
            \Log::error("[CONFIG CLIENT] CREATION DU FICHIER JSON", [
                'path' => $jsonPath,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'message' => $e->getMessage()
            ]);
        }

        // === ÉTAPE 3 : GÉNÉRATION DU PDF REMPLI ===
        $pdfPath = storage_path('app/public/'.$document.'/'.$document.'.pdf'); // Modèle PDF de base
        $outputPath = storage_path('app/public/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF en sortie

        // Vérifie que le JSON existe
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

        // Initialise FPDI (extension TCPDF pour manipuler les PDF existants)
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($pdfPath);
        $totalPages = $pdf->setSourceFile($pdfPath);
        
        // Boucle sur chaque page du modèle pour les dupliquer et les remplir
        for ($i = 1; $i <= $totalPages; $i++) {
            $pdf->AddPage();
            // Import la page du modèle PDF
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0);

            // Remplissage des champs sur la première page uniquement
            if ($i == 1) {
                // Définit la police et la taille du texte (Helvetica, 8pt)
                $pdf->SetFont('helvetica', '', 8);

                // Remplissage des champs avec les coordonnées X,Y spécifiques au modèle
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

        // Génère et sauvegarde le PDF rempli (aplatissement impossible de le modifier après)
        $pdf->Output($outputPath,'F'); 

        // Retourne le PDF en téléchargement
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
        // === ÉTAPE 1 : RÉCUPÉRATION DES PARAMÈTRES ===
        // Accepte les requêtes JSON (POST) ou les paramètres URL (GET)
        if ($request->isJson()) {
            // Requête POST avec JSON
            $data = json_decode($request->getContent(), true);
            $uid = $data['uid'];
            $document = $data['document'];
            $client = $data['client'];
        } else {
            // Requête GET avec paramètres URL
            $uid = $request->query('uid');
            $document = $request->query('document');
            $client = $request->query('client');
        }
        
        // === ÉTAPE 2 : CHEMINS D'ACCÈS AUX FICHIERS ===
        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // Modèle PDF
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // === ÉTAPE 3 : CHARGEMENT DES DONNÉES ===
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

        // Charge les données du fichier JSON
        $data = json_decode(file_get_contents($jsonPath), true);

        // === ÉTAPE 4 : GÉNÉRATION ET REMPLISSAGE DU PDF ===
        // Initialise FPDI pour manipuler le PDF
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($pdfPath);
        $totalPages = $pdf->setSourceFile($pdfPath);
        
        // Boucle sur toutes les pages du modèle
        for ($i = 1; $i <= $totalPages; $i++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($i);
            $pdf->useTemplate($tplIdx, 0, 0);

            if ($i == 1) {
                // ===== REMPLISSAGE DE LA PAGE 1 =====
                // Définit la police (Helvetica 8pt pour s'adapter à la présentation du formulaire)
                $pdf->SetFont('helvetica', '', 8);

                // === SECTION 1 : DONNÉES PERSONNELLES ===
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
                
                // === SECTION 2 : TYPE DE LOGEMENT (NATURE DES LOCAUX) ===
                // Coche la case correspondante au type de logement
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
                    // Si type "autre", coche la case ET remplit le champ de description
                    $pdf->SetXY(19.5, 80.6);
                    $pdf->Write(10, 'X'); 

                    $pdf->SetXY(96, 80);
                    $pdf->Write(10, $data['nature_locaux_type_autre_valeur']); 
                }

                // === SECTION 3 : AFFECTATION DES LOCAUX ===
                // Sélectionne le type d'affectation du logement (résidence principale, etc.)
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

                // === SECTION 4 : STATUT DU PROPRIÉTAIRE ===
                // Indique si le demandeur est propriétaire, locataire, ou autre
                if ($data['nature_locaux_status'] == 'proprietaire') {
                    $pdf->SetXY(36.5, 123.5);
                    $pdf->Write(10, 'X');  // Propriétaire
                }
                else if ($data['nature_locaux_status'] == 'locataire') {
                    $pdf->SetXY(59.5, 123.5);
                    $pdf->Write(10, 'X');  // Locataire
                }
                else if ($data['nature_locaux_status'] == 'status_autre') {
                    $pdf->SetXY(84.5, 123.5);
                    $pdf->Write(10, 'X');  // Autre statut
                    
                    $pdf->SetXY(130, 123);
                    $pdf->Write(10, $data['nature_locaux_status_autre_valeur']); 
                }
                
                // === SECTION 5 : TYPES DE TRAVAUX EFFECTUÉS ===
                // La section suivante liste les différentes catégories de travaux (isolation, chauffage, etc.)
                if (isset($data['travaux']) && is_array($data['travaux'])) {
                    foreach ($data['travaux'] as $travaux) {
                        if ($travaux == "travaux_1") {
                            $pdf->SetXY(19.5, 145);
                            $pdf->Write(10, 'X');  // Type de travaux #1
                        }
                        else if ($travaux == "travaux_2") {
                            $pdf->SetXY(19.5, 152.5);
                            $pdf->Write(10, 'X');  // Type de travaux #2 (Isolation thermique)
                            
                            // === SOUS-SECTION : DÉTAILS DE L'ISOLATION THERMIQUE ===
                            // Spécifie quelle partie de bâtiment a été isolée (toiture, parois, etc.)
                            if (isset($data['travaux_2_details']) && is_array($data['travaux_2_details'])) {
                                foreach ($data['travaux_2_details'] as $travaux_2_details) {
                                    if ($travaux_2_details == "travaux_2_details_1") {
                                        $pdf->SetXY(96, 156.2);
                                        $pdf->Write(10, 'X');  // Détail isolation #1
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_2") {
                                        $pdf->SetXY(33.2, 160);
                                        $pdf->Write(10, 'X');  // Détail isolation #2
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_3") {
                                        $pdf->SetXY(66.7, 160);
                                        $pdf->Write(10, 'X');  // Détail isolation #3
                                    }    
                                    else if ($travaux_2_details == "travaux_2_details_4") {
                                        $pdf->SetXY(97.3, 160);
                                        $pdf->Write(10, 'X');  // Détail isolation #4
                                    }
                                    else if ($travaux_2_details == "travaux_2_details_5") {
                                        $pdf->SetXY(154, 160);
                                        $pdf->Write(10, 'X');  // Détail isolation #5
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


                // === SECTION FINALE : DATES, LIEUX ET SIGNATURE ===
                // Remplir le lieu où les travaux ont été effectués
                $pdf->SetXY(93, 256);
                $pdf->Write(10, $data['fait_a']); 

                // Remplir la date de signature (au format français JJ/MM/YYYY)
                $pdf->SetXY(130, 256);
                $pdf->Write(10, ($this->formatDate($data['fait_le']) ?? date('d/m/Y'))); 

                // === GESTION DE LA SIGNATURE NUMÉRIQUE ===
                // Convertir la signature depuis le format base64 (stockée lors de la capture) vers PNG
                $signatureBase64 = $data['signature'];
                $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
                $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature.png');
                // Sauvegarde l'image PNG temporaire sur le serveur
                file_put_contents($signaturePath, $signatureData);
                // Insère l'image de signature au bon endroit du PDF (position 150, 260 avec dimensions 50x16 mm)
                $pdf->Image($signaturePath, 150, 260, 50, 16);


            }

        }

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        return response()->file($outputPath, [
            'Content-Disposition' => 'inline; filename="' . $uid . '"'
        ]);
    }

    
    public function checkExistAndIsValidePdf($jsonPath, $client, $document, $uid): bool 
    {
        // return false; // Désactiver la validation du PDF pour éviter les problèmes de génération, à réactiver une fois les problèmes résolus

        // === ÉTAPE 1 : DÉTERMINER LE CHEMIN DU FICHIER PDF ===
        // Utilise le chemin JSON comme base si fourni, sinon construit le chemin complet
        $pdfFile = $jsonPath 
            ? str_replace('.json', '.pdf', $jsonPath) 
            : storage_path("app/public/{$client}/{$document}/{$uid}/{$uid}.pdf");
    
        // === ÉTAPE 2 : VÉRIFIER L'EXISTENCE DU FICHIER ===
        if (!File::exists($pdfFile)) {
            return false;
        }
    
        // === ÉTAPE 3 : VALIDATION DE LA STRUCTURE PDF ===
        // Lit les 5 premiers octets pour vérifier la signature PDF (doit commencer par "%PDF-")
        $header = file_get_contents($pdfFile, false, null, 0, 5);
        
        // Calcule la taille totale du fichier pour vérifier la fin
        $fileSize = File::size($pdfFile);
        // Les 6 derniers octets doivent contenir "%%EOF" (marqueur de fin de fichier PDF)
        $footer = ($fileSize > 6) ? file_get_contents($pdfFile, false, null, $fileSize - 6) : '';
    
        // === VALIDATION FINALE ===
        // Un PDF valide commence par "%PDF-" et se termine par "%%EOF"
        $isValidPdf = str_starts_with($header, '%PDF-') && str_contains($footer, '%%EOF');
        
        return $isValidPdf;
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
        $token = $request->query('token');
        $uid = $request->query('uid');
        $document = $request->query('document');
        $client = $request->query('client');
        $isAndroid = $request->query('isAndroid');

        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // PDF d'origine
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré

        // Lire le fichier JSON
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json');
        if (!file_exists($jsonPath)) {
            \Log::error("[DOCUMENT] FICHIER JSON INTROUVABLE", [
                'client' => $client,
                'uid' => $uid,
                'fonction' => __FUNCTION__,
                'fichier' => basename(__FILE__),
                'ligne' => __LINE__,
                'chemin' => $jsonPath
            ]);
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

        if ($this->checkExistAndIsValidePdf($jsonPath, $client, $document, $uid)) {
            \Log::info("[DOCUMENT] FICHIER PDF DEJA EXISTANT", [
                'client' => $client,
                'document' => $document,
                'uid' => $uid,
                'chemin' => $jsonPath
            ]);
            return response()->file(str_replace('.json', '.pdf', $jsonPath), [
                'Content-Disposition' => 'inline; filename="' . $uid . '"'
            ]);
        }

         // === ÉTAPE 1 : CHARGEMENT ET RÉCUPÉRATION DES DONNÉES ===
        // Charge les données du fichier JSON contenant tous les détails du bulletin d'intervention
        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $data = rescue(
            fn() => JsonReader::fromPath($client, $document, $uid, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );

        // === ÉTAPE 2 : INITIALISATION DE FPDI ===
        // FPDI (Free PDF Import) permet de charger un modèle PDF et d'ajouter du contenu par-dessus
        $pdf = new Fpdi();

        // Evite les lignes au dessous et au dessus du PDF et les marges automatiques
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        // === ÉTAPE 3 : REMPLISSAGE DES CHAMPS PRINCIPAUX ===
        
        // Numéro de bulletin (identifiant unique)
        $pdf->SetFont('helvetica', 'b', 8);
        $pdf->SetXY(23, 37);
        $pdf->Write(10, ($this->reformaterTexte($data['dataToken']['uid']) ?? '000'));    

        // Date de l'intervention
        $pdf->SetFont('helvetica', 'b', 8);
        $pdf->SetXY(31, y: 44);
        $pdf->Write(10, ( $this->formatDate($data['date_intervention']) ?? date('d/m/Y')));    
        
        // Nom de l'intervenant principal
        $pdf->SetXY(32, 54);
        $pdf->Write(10, ($data['intervenant'] ?? ''));    
        
        // === SECTION SPÉCIALE : GESTION DU NOM DE L'ÉQUIPIER AVEC POLICE ADAPTATIVE ===
        $equipier = $data['equipier'] ?? '';
        $maxWidth = 38; // On réserve 38mm pour le champ de l'équipier
        $fontSize = 6.7; // Taille de police par défaut

        // BOUCLE MAGIQUE : Réduit la police progressivement si le texte est trop large
        // Cela permet d'accueillir des noms longs sans débordement
        while ($pdf->GetStringWidth($equipier) > $maxWidth && $fontSize > 3) {
            $fontSize -= 0.3; // Réduit par étapes de 0.3 points
            $pdf->SetFont('helvetica', 'b', $fontSize);
        }

        $pdf->SetXY(28.2, 63);
        $pdf->Cell($maxWidth, 4, $equipier, 0, 0, 'L', 0, '', 1);
        
        // === ÉTAPE 4 : REMPLISSAGE DES INFORMATIONS CLIENT ===
        $pdf->SetFont('helvetica', '', 6.7);

        // Code client unique
        $pdf->SetXY(37.25, 66);
        $pdf->Write(10, ($data['code_client'] ?? ''));  
        
        // Email du client
        $pdf->SetXY(29, 71);
        $pdf->Write(10, ($data['email_client'] ?? ''));  
        
        // Téléphone du client
        $pdf->SetXY(142, 66.25);
        $pdf->Write(10, ($data['telephone_client'] ?? ''));  
        $pdf->SetXY(139, 71.5);
        $pdf->Write(10, ($data['portable_client'] ?? ''));  
        
        $pdf->SetFont('helvetica', '', 6.7);

        $pdf->SetXY(14, 88);
        $pdf->MultiCell(180, 10, ($data['description']."\n" ?? ''));

        
	    $pdf->SetFont('helvetica', '', 8); 

        $pdf->SetXY(75, 45);
        $pdf->Write(10, ($data['adresse_intervention'] ?? '') . ' ' . ($data['cp_intervention'] ?? '') . ' ' . ($data['ville_intervention'] ?? '') . ' - ' . ($data['lieu_intervention'] ?? ''));  

        $pdf->SetFont('helvetica', '', 8);

        $pdf->SetXY(75, 59.5);
        $pdf->Write(10, ($data['adresse_facturation'] ?? '') . ' ' . ($data['cp_facturation'] ?? '') . ' ' . ($data['ville_facturation'] ?? ''));


        $pdf->SetFont('helvetica', '', 6.3);

        $pdf->SetXY(15, 111);
        $pdf->MultiCell(180, 10, (($this->reformaterTexte($data['compte_rendu']) ?? '')), 0, 'L');

        $pdf->SetFont('helvetica', '', 6.7);

        $pdf->SetXY(67, 194);
        $pdf->MultiCell(131, 10, (($this->reformaterTexte($data['materiel']) ?? '')), 0, 'L');

        $pdf->SetFont('helvetica', '', 9);

        $pdf->SetXY(67, 229);
        $pdf->MultiCell(131, 10, (($this->reformaterTexte($data['prevoir']) ?? '')), 0, 'L');

        $pdf->SetFont('helvetica', '', 11);
        if (isset($data['intervention_realisable']) && ($data['intervention_realisable'] == 'oui')) {            
            $pdf->SetXY(15.15, 127.6);
            $pdf->Write(10, 'X');
        }        
        if (isset($data['terminee']) && ($data['terminee'] == 'oui')) {            
            $pdf->SetXY(15.15, 143);
            $pdf->Write(10, 'X');
        }
        if (isset($data['intervention_suite']) && ($data['intervention_suite'] == 'oui')) {            
            $pdf->SetXY(15.15, 149.7);
            $pdf->Write(10, 'X');
        }
        if (isset($data['facturable']) && ($data['facturable'] == 'oui')) {            
            $pdf->SetXY(15.15, 164.5);
            $pdf->Write(10, 'X');
        }
        if (isset($data['devis_a_faire']) && ($data['devis_a_faire'] == 'oui')) {            
            $pdf->SetXY(15.15, 157.3);
            $pdf->Write(10, 'X');
        }

        if (isset($data['absent']) && ($data['absent'] == 'oui')) {            
            $pdf->SetXY(15.15, 133.5); 
            $pdf->Write(10, 'X');
        }

        $pdf->SetFont('helvetica', '', 9);

        // === GESTION DES PHOTOS AVANT/APRÈS ===
        // Les photos sont redimensionnées intelligemment en conservant leur ratio d'aspect
        // et centrées dans des zones préallouées du formulaire
        
        if (isset($data['photo_avant'])) {
            $imagePath = storage_path('app/public/'.$data['photo_avant']);
            if ($imagePath && file_exists($imagePath)) {
                // Récupère les dimensions originales de l'image
                list($width, $height) = getimagesize($imagePath);
                
                // Définit la taille maximale pour la photo avant
                $maxWidth = 62;
                $maxHeight = 50;
                
                // === ALGORITHM DE REDIMENSIONNEMENT INTELLIGENT ===
                // Conserve le ratio d'aspect tout en s'adaptant à la zone disponible
                if ($width > $height) {
                    // Image en paysage : limiter la largeur
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    // Image en portrait : limiter la hauteur
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                
                // === CENTRAGE DE L'IMAGE DANS SA ZONE ===
                // Calcule les décalages X et Y pour centrer l'image redimensionnée
                $x = 68.5 + ($maxWidth - $newWidth) / 2;
                $y = 134 + ($maxHeight - $newHeight) / 2;

                // Insertion de l'image redimensionnée et centrée
                $pdf->Image($imagePath, $x, $y, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
        }

        // Photo après l'intervention (même logique que photo_avant)
        if (isset($data['photo_apres'])) {
            $imagePath = storage_path('app/public/'.$data['photo_apres']);
            if ($imagePath && file_exists($imagePath)) {
                list($width, $height) = getimagesize($imagePath); // Récupère la taille originale
                $maxWidth = 60;
                $maxHeight = 48;
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


        // === GESTION DE LA SIGNATURE NUMÉRIQUE ===
        // La signature est capturée en base64 et convertie en image PNG
        if (isset($data['signature'])) {
            $signatureBase64 = $data['signature'];
            // Décode la signature depuis le format base64
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
            $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature.png');
            // Sauvegarde l'image PNG temporaire
            file_put_contents($signaturePath, $signatureData);
            
            // === DIMENSIONS ET POSITION FIXES ===
            // Les signatures ont une taille fixe indépendamment du dispositif (PC/téléphone)
            // Cette approche garantit une présentation cohérente dans tous les cas
            $signatureWidth = 50;  // Largeur fixe : 50 mm
            $signatureHeight = 15; // Hauteur fixe : 15 mm
            
            // Position absolue dans le PDF
            $xImage = 12;  // Position X : 12 mm du bord gauche
            $yImage = 197; // Position Y : 197 mm du haut
            
            // Insère l'image de signature
            $pdf->Image($signaturePath, $xImage, $yImage, $signatureWidth, $signatureHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);    
        }

        // Date de signature (lieu + date)
        $pdf->SetFont('helvetica', 'i', 12);
        $pdf->SetXY( 40, 212);
        $pdf->Write(12, $this->formatDate($data['fait-le']) ?? date('d/m/Y'));

        // Nom de l'intervenant principal qui a signé
        $pdf->SetFont('helvetica', 'i', 14);
        $pdf->SetXY( 14, 226);
        $pdf->Write(12, $data['intervenant'] ?? 'Intervenant');


        // === PAGE 2 : COMPLÉMENTS D'INFORMATION ===
        // Charge la deuxième page du modèle PDF
        if (!empty($data['constat']) || !empty($data['verification']) || !empty($data['notes_particulieres']) || !empty($data['points_vigilances']))
        {
            $tpl2Idx = $pdf->importPage(2);
            $pdf->addPage();
            $pdf->useTemplate($tpl2Idx);

            $pdf->Ln(8);

            // === SECTION 1 : LE CONSTAT ===
            $pdf->SetFont('helvetica', 'b', 9.5);
            $pdf->SetXY( 15, 40);
            $pdf->Write(8, "Le constat :");
            $pdf->Ln(6);
            $pdf->SetFont('helvetica', 9);
            $pdf->SetXY(18, 48);
            $pdf->MultiCell(0, 8, (($this->reformaterTexte($data['constat']) ?? '')), 0, 'L');
            $pdf->Ln(12);

            // === SECTION 2 : LES VÉRIFICATIONS EFFECTUÉES ===
            $pdf->SetFont('helvetica', 'b', 9.5);
            $pdf->SetXY( 15, 85);
            $pdf->Write(8, "Les vérifications :");
            $pdf->Ln(6);
            $pdf->SetFont('helvetica', 9);
            $pdf->SetXY( 18, 93);
            $pdf->MultiCell(0, 8, (($this->reformaterTexte($data['verification']) ?? '')), 0, 'L');
            $pdf->Ln(12);

            // === SECTION 3 : LES NOTES PARTICULIÈRES ===
            $pdf->SetFont('helvetica', 'b', 9.5);
            $pdf->SetXY( 15, 130);
            $pdf->Write(8, "Les notes particulières :");
            $pdf->Ln(6);
            $pdf->SetFont('helvetica', 9);
            $pdf->SetXY( 18, 138);
            $pdf->MultiCell(0, 8, (($this->reformaterTexte($data['notes_particulieres']) ?? '')), 0, 'L');
            $pdf->Ln(12);

            // === SECTION 4 : LES POINTS DE VIGILANCE ===
            $pdf->SetFont('helvetica', 'b', 9.5);
            $pdf->SetXY( 15, 175);
            $pdf->Write(8, "Les points de vigilances :");
            $pdf->Ln(6);
            $pdf->SetFont('helvetica', 9);
            $pdf->SetXY( 18, 183);
            $pdf->MultiCell(0, 8, (($this->reformaterTexte($data['points_vigilances']) ?? '')), 0, 'L');

            // === SECTION 7 : COMPLÉMENTS CLIENTS (SI EXISTANTS) ===
            // Affiche les données personnalisées fournies par le client (textes et images)
            if (!empty($data['complement_client'])) {
                $pdf->AddPage();
                
                // === TITRE DE LA PAGE ===
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor(50, 50, 50);
                $pdf->Cell(0, 10, 'Informations complémentaires client', 0, 1, 'L');
                $pdf->Ln(2);
            
                // === BOUCLE SUR LES ÉLÉMENTS PERSONNALISÉS ===
                foreach ($data['complement_client'] as $item) {
                    // === GESTION DES SAUTS DE PAGE ===
                    // Si moins de 40mm restent, on change de page
                    if ($pdf->GetY() > 250) {
                        $pdf->AddPage();
                    }
            
                    // === AFFICHAGE DE LA QUESTION (EN MAJUSCULES ET EN GRAS) ===
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->SetTextColor(100, 100, 100);
                    $pdf->MultiCell(0, 6, mb_strtoupper($item['question']), 0, 'L');
                    
                    // === AFFICHAGE DE LA RÉPONSE (VALEUR) ===
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 10);
            
                    if ($item['type'] === 'text') {
                        // Affichage du texte simple avec indentation
                        $pdf->SetX(15); 
                        $pdf->MultiCell(0, 6, $item['value'], 0, 'L');
                        $pdf->Ln(2);
                    } else {
                        // Affichage d'une image
                        $imagePath = storage_path('app/public/' . $item['value']);
                        if (file_exists($imagePath)) {
                            $pdf->Ln(1);
                            $currentY = $pdf->GetY();
                            
                            // Affiche l'image (max 60mm large, 40mm haut)
                            $pdf->Image($imagePath, 15, $currentY, 0, 40, '', '', 'T', true, 300, '', false, false, 0, 'L', false, false);
                            
                            // Décale le curseur Y après l'image (images = ~42mm)
                            $pdf->SetY($currentY + 42); 
                        }
                    }
            
                    // === LIGNE DE SÉPARATION SUBTILE ===
                    $pdf->SetDrawColor(230, 230, 230);
                    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
                    $pdf->Ln(4);
                }
            }
        }

        // === SECTION 6 : COMPLÉMENTS SUPPLÉMENTAIRES (SI EXISTANTS) ===
        // Ajoute les compléments sur une page supplémentaire si il y en a plus de 0
        if (!empty($data['complements']) && count($data['complements']) > 0) {
            $pdf->addPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(0, 10, 'Les photos : ', 0, 1, 'L');
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', 'B', 11);
            // === CONFIGURATION DE LA GRILLE D'IMAGES ===
            // Les photos sont affichées en grille 3 colonnes pour une meilleure présentation
            $pageWidth = 190;            // Largeur utile de la page (A4 - marges: ~210mm - 20mm)
            $numCols = 3;                // Nombre d'images par ligne
            $margin = 5;                 // Espace horizontal entre les photos (en mm)
            $cellWidth = ($pageWidth - ($margin * ($numCols - 1))) / $numCols; // Largeur de chaque cellule
            $imgHeightMax = 45;          // Hauteur max d'une image (en mm)
            $commentHeight = 15;         // Espace réservé au commentaire sous l'image
            $rowHeight = $imgHeightMax + $commentHeight + 5; // Hauteur totale d'une ligne (image + texte + espace)
    
            $startX = 10;
            $currentX = $startX;
            $currentY = $pdf->GetY();
                
            // On stocke les compléments à afficher
            $complementsAffiches = $data['complements'];
            
            foreach ($complementsAffiches as $index => $complement) {
                // === GESTION DES SAUTS DE PAGE ===
                // Vérifie si il y a assez de place pour la ligne suivante
                if ($currentY + $rowHeight > 275) {
                    $pdf->addPage();
                    $currentY = 20;
                }
            
                // === AFFICHAGE DE L'IMAGE ===
                if (isset($complement['image'])) {
                    $imagePath = storage_path('app/public/' . $complement['image']);
                        
                    if (file_exists($imagePath)) {
                        // Affiche l'image avec le paramètre 'T' (top alignment) et redimensionnement proportionnel
                        $pdf->Image($imagePath, $currentX, $currentY, $cellWidth, $imgHeightMax, '', '', 'T', true, 300, '', false, false, 0, 'CM', false, false);
            
                        // === AFFICHAGE DU COMMENTAIRE SOUS L'IMAGE ===
                        if (!empty($complement['comment'])) {
                            $pdf->SetFont('helvetica', '', 8);
                            $pdf->SetXY($currentX, $currentY + $imgHeightMax + 1);
                            // MultiCell gère les retours à la ligne si le texte est trop long
                            $pdf->MultiCell($cellWidth, 4, $complement['comment'], 0, 'C', false, 1);
                        }
                    }
                }
            
                // === CALCUL DE LA POSITION SUIVANTE ===
                if (($index + 1) % $numCols == 0) {
                    // On a rempli 3 colonnes, on descend à la ligne suivante
                    $currentX = $startX;
                    $currentY += $rowHeight;
                } else {
                    // On se décale vers la droite
                    $currentX += $cellWidth + $margin;
                }
            }
        }

    // === FINALISATION ===
    // Aplatir le PDF en l'empêchant d'être modifié
    $pdf->Output($outputPath,'F'); 

    return response()->file($outputPath, [
        'Content-Disposition' => 'inline; filename="' . $uid . '"'
    ]);
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
        // === ÉTAPE 1 : RÉCUPÉRATION DES PARAMÈTRES ===
        $uid = $request->query('uid');
        $document = $request->query('document');
        $client = $request->query('client');
        
        // === ÉTAPE 2 : CHEMINS DES FICHIERS ===
        $pdfPath = storage_path('app/public/'.$client.'/'.$document.'/'.$document.'.pdf'); // PDF modèle
        $outputPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.pdf'); // PDF généré
        $jsonPath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'.json'); // Données JSON

        // === ÉTAPE 3 : VALIDATION - FICHIER JSON EXISTE-T-IL ? ===
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'Fichier JSON '.$jsonPath.' non trouvé'], 404);
        }

        // === ÉTAPE 4 : VÉRIFIER SI LE PDF EST DÉJÀ GÉNÉRÉ ===
        if ($this->checkExistAndIsValidePdf($jsonPath, $client, $document, $uid)) {
            \Log::info("[DOCUMENT] FICHIER PDF DEJA EXISTANT", [
                'client' => $client,
                'document' => $document,
                'uid' => $uid,
                'chemin' => $jsonPath
            ]);
            return response()->file(str_replace('.json', '.pdf', $jsonPath), [
                'Content-Disposition' => 'inline; filename="' . $uid . '"'
            ]);
        }

        // === ÉTAPE 5 : CHARGEMENT DES DONNÉES ===
        // Récupère tous les données du formulaire depuis le fichier JSON
        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $data = rescue(
            fn() => JsonReader::fromPath($client, $document, $uid, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );

        // === ÉTAPE 6 : INITIALISATION DE FPDI ===
        // Charge le modèle PDF CERFA 15497-03
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        // === ÉTAPE 7 : CONFIGURATION GÉNÉRALE DU TEXTE ===
        $pdf->SetFont('helvetica', '', 8);

        // === REMPLISSAGE DES CHAMPS PRINCIPAUX ===
        // Numéro de formulaire unique
        $pdf->SetXY(13, 32);
        $pdf->MultiCell(30, 0, ($uid ?? ''));

        // Police et couleur standard pour les informations principales
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        // === SECTION 1 : IDENTIFICATION OPÉRATEUR ET DÉTENTEUR ===
        $largeur = 60;
        
        // Opérateur (entreprise effectuant l'intervention)
        $pdf->SetXY(44, 32.5);
        $operateur = ($data['operateur'] ?? "") . "\n";
        $pdf->MultiCell($largeur, 10, $operateur);

        // Détenteur (propriétaire/gestionnaire de l'équipement)
        $pdf->SetXY(121, 33);
        $detenteur = ($data['detenteur'] ?? "") . "\n";
        $pdf->MultiCell($largeur, 10, $detenteur);

        $pdf->SetXY(75, 47);
        $pdf->Write(10, ($data['numero_attestation_capacite'] ?? ''));

        $pdf->SetXY(45, 60);
        $identification = ($data['identification'] ?? "") . "\n";
        $pdf->MultiCell(75, 2, $identification);

        $pdf->SetXY(170, 53);
        $pdf->Write(10, ($data['denomination'] ?? ''));

        $pdf->SetXY(167, 58);
        $pdf->Write(10, ($data['charge'] ?? ''));

        $pdf->SetXY(166, 63);
        $pdf->Write(10, ($data['tonnage'] ?? ''));

        if ($data['nature_intervention'] == "assemblage") {
            $pdf->SetXY(54.5, 68.5);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "mise_service") {
            $pdf->SetXY(54.7, 73.3);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "modification") {
            $pdf->SetXY(54.7, 78);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "maintenance") {
            $pdf->SetXY(54.7, 83);
            $pdf->Write(10, 'X');    
        }
        elseif ($data['nature_intervention'] == "controle_periodique") {
            $pdf->SetXY(116, 68.5);
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

        $pdf->SetXY(151, 82);
        $pdf->Write(10, ($data['autre_valeur'] ?? ''));

        $pdf->SetXY(74, 94);
        $pdf->Write(10, ($data['identification_controle'] ?? ''));

        list($year,$month,$day) = explode('-', $data['date_controle']);
        $pdf->SetXY(152, 94);
        $pdf->Write(10, ($day ?? ''));
        $pdf->SetXY(163, 94);
        $pdf->Write(10, ($month ?? ''));
        $pdf->SetXY(175, 94);
        $pdf->Write(10, ($year ?? ''));

        
        $pdf->SetFont('dejavusans', '', 9);
        if ($data['detection_fuites'] == 'non') {
            $pdf->SetXY(147.9, 100.5);
        }
        else {
            $pdf->SetXY(116.5, 100.5);
        }
        $pdf->Write(9, '●');
        
        $pdf->SetFont('helvetica', '', 8);    

        if (isset($data['hcfc'])) {
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
                $x = 168.5;  
                $y = 109.5;
                $pdf->SetXY($x, $y);
                $pdf->Write(10, 'X');       
            }    
        }
        if (isset($data['hfc_pfc'])) {
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
                $x = 168.5;
                $y = 114;
                $pdf->SetXY($x, $y);
                $pdf->Write(10, 'X');  
            }  
        }
        if (isset($data['hfo'])) {
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
                $x = 168.5;
                $y = 119;
                $pdf->SetXY($x, $y);
                $pdf->Write(10, 'X');  
            }  
        }
        if (isset($data['equipement_sans_detection']))
        {
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
                $x = 168.5;
                $y = 123.2;
                $pdf->SetXY($x, $y);
                $pdf->Write(10, 'X');   
            } 
        }

        if (isset($data['equipement_avec_detection'])) {
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
            elseif ($data['equipement_avec_detection'] == 'avec6') {
                $x = 168.5;
                $y = 128;
                $pdf->SetXY($x, $y);
                $pdf->Write(10, 'X');   
            }
        }
        
        if ($data['constat_fuites'] == 'oui') {
            $pdf->SetXY(22.5, 149.7);
            $pdf->Write(10, 'X');

            // gestion de la localisation des fuites
            $largeur = 100;
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
                $pdf->SetXY(173.5, 155.5);
                $pdf->Write(10, 'X');   
            }
        }
        else {
            $pdf->SetXY(22.5, 153.5);
            $pdf->Write(10, 'X');
        }

        $pdf->SetXY(74, 165);
        $pdf->Write(10, $data['quantite_chargee_totale']);
        $pdf->SetXY(74, 169.5);
        $pdf->Write(10, $data['quantite_chargee_A']);
        $pdf->SetXY(74, 174.2);
        $pdf->Write(10, $data['fluide_A']);
        $pdf->SetXY(88.5, 179);
        $pdf->Write(10, $data['quantite_chargee_B']);
        $pdf->SetXY(88.5, 184);
        $pdf->Write(10, $data['quantite_chargee_C']);

        $pdf->SetXY(181, 165);
        $pdf->Write(10, $data['quantite_recuperee_totale']);
        $pdf->SetXY(181, 169.5);
        $pdf->Write(10, $data['quantite_recuperee_D']);
        $pdf->SetXY(166, 174.2);
        $pdf->Write(10, $data['BSFF']);
        $pdf->SetXY(181, 179);
        $pdf->Write(10, $data['quantite_recuperee_E']);
        $pdf->SetXY(151, 184);
        $pdf->Write(10, $data['identification_E']);

        if ($data['fluide_non_inflammable'] == 'UN1078') {
            $pdf->SetXY(15, 197.5);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['fluide_non_inflammable'] == 'autre_cas_non_inflammable') { 
            $pdf->SetXY(108, 197.5);
            $pdf->Write(10, 'X');   
            
            $pdf->SetXY(164.5 , 197.4);
            $pdf->Write(10, strtolower( $data['autre_fluide_non_inflammable']));   
        }

        if ($data['fluide_inflammable'] == 'UN3161') {
            $pdf->SetXY(15.5, 207);
            $pdf->Write(10, 'X');   
        }
        elseif ($data['fluide_inflammable'] == 'autre_cas_inflammable') { 
            $pdf->SetXY(108, 207);
            $pdf->Write(10, 'X');   
            
            $pdf->SetXY(159, 207);
            $pdf->Write(10, strtolower($data['autre_fluide_inflammable']));   
        }
       
        
        $largeur = 180;
        $pdf->SetXY(13, 218.5);
        $pdf->MultiCell($largeur, 10, ($data['installation_destination_fluide']."\n" ?? ''));

        $pdf->SetXY(13, 231.5);
        $pdf->MultiCell($largeur, 10, ($data['observations']."\n" ?? '')); 
        

        //Gestion des signatures
        $pdf->SetXY(45, 258);
        $pdf->Write(10, ($data['nom_signataire_operateur'] ?? ''));   
        $pdf->SetXY(45, 264.5);
        $pdf->Write(10, ($data['qualite_signataire_operateur'] ?? ''));  
        $pdf->SetXY(45, 274);
        $pdf->Write(10, ($data['date_signature_operateur'] ?? '')); // Possiblement a refaire pour formater la date dans le format français

        $signatureBase64 = $data['signature-operateur'];
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
        $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature_operateur.png');
        file_put_contents($signaturePath, $signatureData);
        // Taille fixe pour la signature opérateur
        $pdf->Image($signaturePath, 67, 275, 35, 6.5);
        
        $pdf->SetXY(125, 258);
        $pdf->Write(10, ($data['nom_signataire_detenteur'] ?? ''));   
        $pdf->SetXY(125, 264.5);
        $pdf->Write(10, ($data['qualite_signataire_detenteur'] ?? ''));  
        $pdf->SetXY(125, 274);
        $pdf->Write(10, ($data['date_signature_detenteur'] ?? ''));  // Possiblement a refaire pour formater la date dans le format français

        $signatureBase64 = $data['signature-detenteur'];
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
        $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature_detenteur.png');
        file_put_contents($signaturePath, $signatureData);
        // Taille fixe pour la signature détenteur
        $pdf->Image($signaturePath, 147, 275, 35, 6.5);

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
     * @throws \Exception Si le fichier n'est pas un PDF valide.
     * @throws \Exception Si le token n'existe pas en base.
     * @throws \Exception Si la création du répertoire ou le déplacement du fichier échoue.
     *
     * @note L'arborescence créée suit le pattern : {organisation_id}/devis/{devis_id}_{token}/
     * @note Tous les uploads sont tracés dans les logs pour audit et sécurité.
     * @warning Seuls les fichiers PDF sont acceptés pour des raisons de sécurité.
     * @par Exemple:
     * POST /upload avec un fichier "ABC123.pdf" crée le chemin ORG001/devis/DEV456_ABC123/DEV456_ABC123.pdf
     */


public function upload(Request $request)
{
    // 1. Validation stricte
    $request->validate([
        'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10Mo
        'token'    => 'required|string'
    ]);

    $token = $request->token;

    // ==========================================
    // VÉRIFICATION DU TOKEN ET DES DONNÉES
    // ==========================================
    $dataToken = TokenLinks::where('token', $token)
        ->where('expires_at', '>', now())
        ->first();

    if (!$dataToken) {
        Log::warning("[SIGNATURE] Token introuvable, déjà utilisé ou expiré", ['token' => $token]);
        return response()->json(['message' => 'Lien du token invalide ou expiré.'], Response::HTTP_FORBIDDEN);
    }

    // Permet en cas de d'excption de la capturer et d'utiliser abort()
    $data = rescue(
        fn() => JsonReader::fromToken($dataToken, __CLASS__),
        fn() => abort(500, "Erreur lors de la récupération de vos données.")
    );

    // 3. Vérification de sécurité (Expiration et Usage)
    if ($data["used"]) {
        return response()->json(['message' => 'Ce devis a déjà été signé/validé'], 403);
    }

    $devis_id = $data["dataToken"]["devis_id"];
    $organisation_id = $data["dataToken"]["organisation_id"];

    try {
        $file = $request->file('pdf_file');
        
        // Construction du nom et du dossier
        $fileName = $devis_id . ".pdf";
        
        // Chemin relatif : organisation_id/devis/devis_id_token/
        $relativePath = "{$organisation_id}/devis/{$devis_id}";

        // 4. Stockage automatique (Laravel crée les dossiers tout seul)
        // On utilise le disk 'public' (configuré dans config/filesystems.php)
        $path = $file->storeAs($relativePath, $fileName, 'public');

        // 5. Log avec les bonnes variables
        \Log::info("Fichier PDF uploadé avec succès", [
            'token' => $token,
            'devis_id' => $devis_id,
            'nom_fichier' => $fileName,
            'organisation' => $organisation_id,
            'chemin_stockage' => $path,
        ]);

        return response()->json([
            'message' => 'Upload réussi', 
            'path' => $path,
            'original_filename' => $file->getClientOriginalName()
        ], 200);

    }
    catch(\Exception $e) {
        \Log::info("Fichier PDF uploadé avec succès", [
            'token' => $token,
            'devis_id' => $devis_id,
            'nom_fichier' => $fileName,
            'organisation' => $organisation_id,
            'chemin_complet' => $path,
        ]);

        return response()->json([
            'message' => 'Upload fail', 
            'error' => "{$e}"
        ], 500);
    }
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
     * @throws \Exception Si le token n'existe pas en base.
     *
     * @note La vue reçoit : client, token, nomDevis, isCertified pour l'affichage conditionnel.
     * @note La vérification de certification recherche un fichier avec suffixe '_certifie.pdf'.
     * @warning Aucune vérification d'autorisation supplémentaire n'est effectuée au-delà de l'existence du token.
     * @par Exemple:
     * GET /devis/ABC123 affiche le devis associé au token "ABC123" avec son statut de certification.
     */
    public function viewDevis(Request $request, $token){
        $dataToken = TokenLinks::where('token', $token)->first();

        if (!$dataToken) {
            Log::warning("[SIGNATURE] Token introuvable, déjà utilisé ou expiré", ['token' => $token]);
            return response()->json(['message' => 'Lien du token invalide ou expiré.'], Response::HTTP_FORBIDDEN);
        }

        // Permet en cas de d'excption de la capturer et d'utiliser abort()
        $data = rescue(
            fn() => JsonReader::fromToken($dataToken, __CLASS__),
            fn() => abort(500, "Erreur lors de la récupération de vos données.")
        );

        $devis_id = $data["dataToken"]["devis_id"];
        $organisation_id = $data["dataToken"]["organisation_id"];

        $devisName = $devis_id;
        $certifiedPath = "{$organisation_id}/devis/{$devisName}/{$devisName}_certifie.pdf";
        $isCertified = Storage::disk('public')->exists($certifiedPath);

        return view('devis_pdf', [
            'client' => $organisation_id,
            'token' => $token,
            'nomDevis' => $devis_id,
            'isCertified' => $isCertified
        ]);
    }


    static public function generateDevisPdf(string $pdfOriginalPath, string $outputPath, string $signaturePath, array $data): bool 
    {
        try {
            // Sécurisation : on vérifie si "coords" est une chaîne (JSON) ou déjà un tableau
            $coords = is_string($data["coords"]) ? json_decode($data["coords"], true) : $data["coords"];

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pageCount = $pdf->setSourceFile($pdfOriginalPath);
            
            // CORRECTION DU BUG : Si pageCount = 1 et nb_pages = 1, ça donnait 0. On force minimum la page 1.
            $pagesAEnlever = isset($data["nb_pages"]) ? (int)$data["nb_pages"] : 0;
            $pageSignature = max(1, $pageCount - $pagesAEnlever); 

            Log::info("[PDF] Début du traitement des pages", [
                'total_pages' => $pageCount, 
                'page_signature' => $pageSignature
            ]);

            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($i);
                $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

                // Application de la signature sur la bonne page
                if ($i === $pageSignature) {
                    $ratioConversion = 6.98;
                    
                    // 1. Ajout de la date
                    $xDate = ($coords["x_date"] ?? 0) / $ratioConversion;
                    $yDate = ($coords["y_date"] ?? 0) / $ratioConversion;
                    
                    $pdf->SetXY($xDate, $yDate);
                    $pdf->Write(10, date('d/m/Y'));

                    // 2. Ajout de la signature
                    list($width, $height) = getimagesize($signaturePath);
                    $maxWidth = 31;
                    $maxHeight = 13;
                    
                    if ($width > $height) {
                        $newWidth = $maxWidth;
                        $newHeight = ($height / $width) * $maxWidth;
                    } else {
                        $newHeight = $maxHeight;
                        $newWidth = ($width / $height) * $maxHeight;
                    }

                    $xImage = ($coords["x_signature"] ?? 0) / $ratioConversion; 
                    $yImage = ($coords["y_signature"] ?? 0) / $ratioConversion;
                    
                    $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    
                    Log::info("[PDF] Signature et date apposées avec succès", ['page' => $i, 'x' => $xImage, 'y' => $yImage]);
                }
            }

            $pdf->Output($outputPath, 'F');
            Log::info("[PDF] Fichier PDF intermédiaire généré avec succès", ['chemin' => $outputPath]);
            
            return true;

        } catch (\Exception $e) {
            Log::error("[PDF] Erreur critique lors de la génération FPDI", [
                'erreur'  => $e->getMessage(),
                'ligne'   => $e->getLine(),
                'fichier' => $e->getFile()
            ]);
            return false; 
        }
    }


    /**
     * Intègre la signature par nom complet et la date dans le PDF original.
     */
    static public function generateDevisPdfFullName(string $pdfPath, string $outputPath, string $signaturePath, array $data): bool
    {
        try {
            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pageCount = $pdf->setSourceFile($pdfPath);

            // Extraction sécurisée des coordonnées
            $coords = isset($data['coords']) ? (is_string($data["coords"]) ? json_decode($data["coords"], true) : $data["coords"]) : [];
            $xDate = $coords['x_date'] ?? 0;
            $yDate = $coords['y_date'] ?? 0;
            $xSignature = $coords['x_signature'] ?? 0;
            $ySignature = $coords['y_signature'] ?? 0;
            
            // Calcul de la page de signature avec sécurité max(1, ...)
            $nbPagesOffsets = isset($data['nb_pages']) ? (int)$data['nb_pages'] : 0;
            $pageSignature = max(1, $pageCount - $nbPagesOffsets);

            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($i);
                $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

                // Application de la signature sur la bonne page
                if ($i === $pageSignature) {
                    $ratioConversion = 6.98;

                    // 1. Intégration de la date
                    $date_signature = date('d/m/Y');
                    $pdf->SetXY($xDate / $ratioConversion, $yDate / $ratioConversion);
                    $pdf->Write(10, $date_signature);

                    // 2. Intégration de la signature
                    list($width, $height) = getimagesize($signaturePath);
                    
                    $maxWidth = 50;  // 50mm pour signature manuscrite
                    $maxHeight = 20; // 20mm
                    
                    if ($width > $height) {
                        $newWidth = $maxWidth;
                        $newHeight = ($height / $width) * $maxWidth;
                    } else {
                        $newHeight = $maxHeight;
                        $newWidth = ($width / $height) * $maxHeight;
                    }
                    
                    $xImage = $xSignature / $ratioConversion; 
                    $yImage = $ySignature / $ratioConversion;
                    
                    $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                }
            }
            
            $pdf->Output($outputPath, 'F');
            return true;

        } catch (\Exception $e) {
            Log::error("[PDF] Erreur génération devis signe FULLNAME", [
                'erreur'  => $e->getMessage(),
                'fichier' => $e->getFile(),
                'ligne'   => $e->getLine()
            ]);
            return false;
        }
    }
}
