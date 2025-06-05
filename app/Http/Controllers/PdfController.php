<?php

namespace App\Http\Controllers;

use App\Models\Token;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class PdfController extends Controller
{

    public function show($client,$document,$uid)
    {
        return view('pdf', compact('client','document', 'uid'));        
    }

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

    public function generateBi(Request $request)
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

        
        $pdf->SetFont('helvetica', 'b', 9);
        $pdf->SetXY(42, 43.2);
        $pdf->Write(10, ($data['uid'] ?? ''));    
        $pdf->SetXY(22, 48.5);
        $pdf->Write(10, ($data['date_intervention'] ?? date('d/m/Y')));    
        $pdf->SetXY(30, 53.5);
        $pdf->Write(10, ($data['intervenant'] ?? ''));    
        $pdf->SetXY(30, 57.5);
        $pdf->Write(10, ($data['equipier'] ?? ''));    

        
        $pdf->SetXY(40, 66);
        $pdf->Write(10, ($data['code_client'] ?? ''));  
        $pdf->SetXY(40, 71);
        $pdf->Write(10, ($data['email_client'] ?? ''));  
        $pdf->SetXY(145, 66);
        $pdf->Write(10, ($data['telephone_client'] ?? ''));  
        $pdf->SetXY(145, 71);
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

        

        $pdf->SetFont('helvetica', '', 12);

        $pdf->SetXY(70, 45);
        $pdf->Write(10, ($data['lieu_intervention'] ?? ''));    
        $pdf->SetXY(70, 50);
        $pdf->Write(10, ($data['adresse_intervention'] ?? ''));  
        $pdf->SetXY(70, 55);
        $pdf->Write(10, $data['cp_intervention'] . ' ' . $data['ville_intervention']);

        $pdf->SetFont('helvetica', '', 9);

        $pdf->SetXY(15, 108);
        $pdf->MultiCell(180, 10, ($data['compte_rendu']."\n" ?? ''));

        $pdf->SetXY(14, 194);
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
                $x = 63 + ($maxWidth - $newWidth) / 2;
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
                $x = 130 + ($maxWidth - $newWidth) / 2;
                $y = 134 + ($maxHeight - $newHeight) / 2;

                // Affichage de l'image redimensionnée
                $pdf->Image($imagePath, $x, $y, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);

            }
        }

        //Gestion des infos complémentaires
        $countComplement = 0;
        $x = 85;
        $y = 194;
        if (isset($data['complements']) && count($data['complements']) > 0) {
            foreach ($data['complements'] as $complement) {
                $countComplement = $countComplement + 1;
                if ($countComplement <= 2) {
                    // Affichage des infos complémentaires
                    if (isset($complement['comment'])) {
                        $pdf->setXY($x,$y);
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
                            $yImage = $y + 14.5 + ($maxHeight - $newHeight) / 2;

                            // Affichage de l'image redimensionnée
                            $pdf->Image($imagePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);    
                        }
                    }
                    $x = $x + 58.5;
                }
            }

            if (isset($data['signature'])) {
                $signatureBase64 = $data['signature'];
                $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
                $signaturePath = storage_path('app/public/'.$client.'/'.$document.'/'.$uid.'/'.$uid.'_signature.png');
                file_put_contents($signaturePath, $signatureData);
                
                list($width, $height) = getimagesize($signaturePath); // Récupère la taille originale
                $maxWidth = 69;
                $maxHeight = 28;
                // Calcul du redimensionnement en conservant le ratio
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = ($height / $width) * $maxWidth;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = ($width / $height) * $maxHeight;
                }
                // Calcul des positions pour centrer dans le carré
                $xImage = 14 + ($maxWidth - $newWidth) / 2;
                $yImage = 230 + ($maxHeight - $newHeight) / 2;
                $pdf->Image($signaturePath, $xImage, $yImage, $newWidth, $newHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);    
                                      
            }
        }

        $x_complement_client = 10; //alignement x des complement client 
        $y_complement_client = 20; //alignement y des complement client 

        if ($data['complement_client'] && count($data['complement_client']) > 0) {
            $complement_client = $data['complement_client'];

            $pdf->AddPage(); 
            $pdf->SetFont('helvetica', 'b', 9);

            // boucle sur les complement client 
            foreach ($data['complement_client'] as $item) { // Donne a $item un tableau avec item et question
                
                if ($item['type'] === 'text') {
                    $pdf->Text($x_complement_client, $y_complement_client, $item['question']);
                    $pdf->Text($x_complement_client + 80, $y_complement_client, ':');

                    $texteFormaterArray = $this->formatTexte($item['value']);

                    foreach ($texteFormaterArray as $key => $value) {
                        if ($key === 0) $pdf->Text($x_complement_client + 100, $y_complement_client, $value);
                        else $pdf->Text($x_complement_client + 5, $y_complement_client, $value);

                        $y_complement_client += 8;
                    }
                    
                }
                else {
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
                
            }

            $pdf->Text($x_complement_client, $y_complement_client, 'Test');

        }

        // Aplatir le PDF en l'empêchant d'être modifié
        $pdf->Output($outputPath,'F'); 

        return response()->file($outputPath, [
            'Content-Disposition' => 'inline; filename="' . $uid . '"'
        ]);
    }

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

    public function viewDevis(Request $request, $token){
        $leToken = Token::where('token', $token)->first();

        if (!$leToken) {
            abort(404);
        }

        $devisName = $leToken->devis_id . '_' . $token;
        $isCertified = Storage::disk('public')->exists($leToken->organisation_id . '/devis/' . $devisName . '/' . $devisName . '_certifie.pdf'); 

        return view('devis_pdf', [
            'client' => $leToken->organisation_id,
            'token' => $token,
            'nomDevis' => $leToken->devis_id,
            'isCertified' => $isCertified
        ]);
    }
}
