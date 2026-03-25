<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Token;
use App\Models\TokenLinksRapport;
use Illuminate\Support\Facades\Log;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Crée le fichier Options_BI.json avec une structure par défaut s'il n'existe pas.
     * * @param string $client Le nom du client (dossier)
     * @return bool True si créé avec succès, False s'il existe déjà ou en cas d'erreur
     */
    public static function createBiOptionFile(string $client): bool 
    {
        $optionFile = "{$client}/Options_BI.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($optionFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "Constat"           => [""],
            "Verification"      => [""],
            "NotesParticuliere" => [""],
            "PointVigilance"    => [""]
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $optionFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[OPTIONS_BI] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }


    public static function copyPdfFileToClientBI(string $client, string $document): bool 
    {
        // Vérification stricte des paramètres (évite les espaces vides)
        if (empty(trim($client)) || empty(trim($document))) {
            return false;
        }

        $sourcePath = "pdf/{$document}.pdf";
        $destinationPath = "{$client}/{$document}/{$document}.pdf"; 

        $localDisk = Storage::disk('local');
        $publicDisk = Storage::disk('public');

        // Vérifier si le fichier source existe
        if (!$localDisk->exists($sourcePath)) {
            return false;
        }

        try {
            // Utilisation des Streams (Flux) pour la copie inter-disques
            // C'est beaucoup plus performant pour les PDF car ça ne charge pas tout le fichier en RAM
            $stream = $localDisk->readStream($sourcePath);
            
            // La méthode put() va créer automatiquement les dossiers de destination s'ils n'existent pas
            $success = $publicDisk->put($destinationPath, $stream);
            
            // Fermer le flux pour libérer la mémoire
            if (is_resource($stream)) {
                fclose($stream);
            }

            return $success;

        } catch (\Exception $e) {
            // 5. Gérer les erreurs inattendues (ex: permissions de dossier)
            Log::error("Erreur lors de la copie du PDF vers le client : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public static function create($client, $document, $uid, array $validated = null): bool
    {
        if (empty($client) || empty($document) || empty($uid) || !$validated) {
            return false;
        }

        // Préparation du chemin et des données
        $relativeFolder = "{$client}/{$document}/{$uid}";
        $relativeFilePath = "{$relativeFolder}/{$uid}.json";

        $jsonData = [
            'dataToken' => [
                'client'   => $client,
                'document' => $document,
                'uid'      => $uid,
            ]
        ];


        try {
            
             // On fusionne les champs extra (ceux qui ne sont pas dans le bloc dataToken)
            $extraFields = array_diff_key($validated, array_flip(['uid', 'client', 'document']));
            $jsonData = array_merge($jsonData, $extraFields);


            $stored = Storage::disk('public')->put(
                    $relativeFilePath, 
                    json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // On s'assure que le fichier a bien été écrit sur le disque
            if (!$stored) {
                throw new \Exception("Échec de l'écriture du fichier JSON sur le disque.");
            }

            $pdfFile = "{$client}/{$document}/{$document}.pdf";

            if ($document === "rapport_intervention") {
                $optionFile = "{$client}/Options_BI.json";

                if (!Storage::disk('public')->exists($optionFile)) {
                    if (!ClientController::createBiOptionFile($client)) {
                        Log::warning("Erreur lors de la création du fichier de configuration du BI : " . $optionFile);
                    }
                }

                if (!Storage::disk('public')->exists($pdfFile)) {
                    if (!ClientController::copyPdfFileToClientBI($client, $document)) {
                        Log::warning("Erreur lors de la copie du fichier pdf : " . $document);
                    }
                }
            }
            // A utiliser plus tard -- Inutiliser actuellement
            else if ($document === "cerfa_15497") {
                $optionFile = "{$client}/Options_Cerfa.json";

                if (!Storage::disk('public')->exists($optionFile)) {
                    if (!ClientController::createBiOptionFile($client)) {
                        Log::warning("Erreur lors de la création du fichier de configuration du BI : " . $optionFile);
                    }
                }

                if (!Storage::disk('public')->exists($pdfFile)) {
                    if (!ClientController::copyPdfFileToClientBI($client, $document)) {
                        Log::warning("Erreur lors de la copie du fichier pdf : " . $document);
                    }
                }
            }

        } catch (\Exception $e) {
            // Log de l'erreur pour le debug
            \Log::error("Erreur lors de la création du fichier JSON: " . $e->getMessage());
            return false;
        }

        return true;
    }


    public static function checkExistClient($client): bool {

    if (empty($client)) {
            return false;
        }

        // Préparation du chemin et des données
        $relativeFolder = "public/{$client}";

        try {
            $stored = Storage::exists($relativeFolder);

            // On s'assure que le fichier a bien été écrit sur le disque
            if (!$stored) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            \Log::error("Erreur lors de la vérification d'existance du dossier client': " . $e->getMessage());
            return false;
        }
    }


    public static function checkExistDocument($client, $document, $uid): bool {

    if (empty($client) || empty($document) || empty($uid)) {
            return false;
        }

        // Préparation du chemin et des données
        $relativeFolder = "{$client}/{$document}/{$uid}";
        $relativeFilePath = "{$relativeFolder}/{$uid}.json";

        try {
            $stored = Storage::exists($relativeFilePath);

            // On s'assure que le fichier a bien été écrit sur le disque
            if (!$stored) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            \Log::error("Erreur lors de la vérification d'existance du document d'un client': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Traite et met à jour les données d'un document existant.
     * 
     * Cette méthode fusionne les données existantes avec les nouvelles entrées de la requête.
     * Le traitement varie selon le type de document (rapport_intervention vs cerfa_15497)
     * car ils ont des structures de données différentes.
     * 
     * @param string $client Le nom du client
     * @param string $document Le type de document (rapport_intervention, cerfa...)
     * @param string $uid L'identifiant unique du document
     * @param Request $request La requête contenant les nouvelles données
     * @param array $existingData Le contenu actuel du fichier JSON
     * @return bool Retourne true si la sauvegarde a réussi, false sinon.
     */
    public static function store($client, $document, $uid, Request $request, array $existingData): bool
    {
        try {
            $data = $existingData;

            // === TRAITEMENT DIFFÉRENCIÉ PAR TYPE DE DOCUMENT ===
            // Chaque type a sa propre logique de mise à jour (champs texte, images, signatures, etc.)
            if ($document == 'rapport_intervention') {
                $biPath = $client . '/' . $document . '/' . $uid;
                
                // Mettre à jour les champs de texte simples
                $textFields = [
                    'intervention_realisable', 'equipier', 'compte_rendu', 'materiel', 
                    'intervention_suite', 'prevoir', 'facturable', 'terminee', 'absent', 
                    'fait-le', 'devis_a_faire', 'constat', 'verification', 
                    'notes_particulieres', 'points_vigilances', 'signature', "date_intervention"
                ];

                foreach ($textFields as $field) {
                    if ($request->has($field)) {
                        // Si le champ est dans la requête, on force null en ""
                        $val = $request->input($field);
                        $data[$field] = $val === null ? "" : $val;
                    } else {
                        // Si le champ n'est pas dans la requête et n'existe pas (ou est null) dans les data existantes
                        if (!isset($data[$field]) || $data[$field] === null) {
                            $data[$field] = "";
                        }
                    }
                }

                // Traitement Photo Avant
                if ($request->hasFile('photo_avant') && $request->file('photo_avant')->isValid()) {
                    $file = $request->file('photo_avant');
                    $data['photo_avant'] = $file->store($biPath, 'public');
                }

                // Traitement Photo Après
                if ($request->hasFile('photo_apres') && $request->file('photo_apres')->isValid()) {
                    $file = $request->file('photo_apres');
                    $data['photo_apres'] = $file->store($biPath, 'public');
                }

                // Traitement des compléments (images + commentaires)
                $data['complements'] = [];
                $comments = $request->input('comments');
                $images = $request->input('images');
                if (isset($images)) {
                    foreach ($images as $index => $image) {
                        $data['complements'][] = [
                            'image' => $biPath . '/' . $image,
                            'comment' => $comments[$index] ?? '', 
                        ];
                    }
                }

                $data['pdf_exists'] = true; // Marquer que le PDF a été généré

            } else if (str_starts_with($document, 'cerfa_15497')) {
                // Mettre à jour les champs spécifiques au CERFA
                $cerfaFields = [
                    'operateur', 'detenteur', 'numero_attestation_capacite', 'identification', 
                    'denomination', 'charge', 'tonnage', 'nature_intervention', 'autre_valeur', 
                    'identification_controle', 'date_controle', 'detection_fuites', 'hcfc', 
                    'hfc_pfc', 'hfo', 'equipement_sans_detection', 'equipement_avec_detection', 
                    'constat_fuites', 'localisation_fuite_1', 'reparation_fuite_1', 
                    'localisation_fuite_2', 'reparation_fuite_2', 'localisation_fuite_3', 
                    'reparation_fuite_3', 'quantite_chargee_totale', 'quantite_chargee_A', 
                    'fluide_A', 'quantite_chargee_B', 'quantite_chargee_C', 'quantite_recuperee_totale', 
                    'quantite_recuperee_D', 'BSFF', 'quantite_recuperee_E', 'identification_E', 
                    'fluide_non_inflammable', 'autre_fluide_non_inflammable', 'fluide_inflammable', 
                    'autre_fluide_inflammable', 'installation_destination_fluide', 'observations',
                    'nom_signataire_operateur', 'qualite_signataire_operateur', 
                    'nom_signataire_detenteur', 'qualite_signataire_detenteur'
                ];

                foreach ($cerfaFields as $field) {
                    if ($request->has($field)) {
                        // Si le champ est dans la requête, on force null en ""
                        $val = $request->input($field);
                        $data[$field] = $val === null ? "" : $val;
                    } else {
                        // Si le champ n'est pas dans la requête et n'existe pas (ou est null) dans les data existantes
                        if (!isset($data[$field]) || $data[$field] === null) {
                            $data[$field] = "";
                        }
                    }
                }

                // Gestion des signatures et dates (remplacement des null par "")
                $data['date_signature_operateur'] = date('d/m/Y');
                $data['signature-operateur'] = $request->input('signature-operateur') ?: "";

                $data['date_signature_detenteur'] = date('d/m/Y');
                $data['signature-detenteur'] = $request->input('signature-detenteur') ?: "";
            } 

            // Sauvegarde sécurisée via la façade Storage de Laravel
            $relativeFolder = "{$client}/{$document}/{$uid}";
            $relativeFilePath = "{$relativeFolder}/{$uid}.json";

            $stored = Storage::disk('public')->put(
                $relativeFilePath, 
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            if (!$stored) {
                throw new \Exception("Échec de l'écriture du fichier JSON mis à jour sur le disque.");
            }

            return true;

        } catch (\Exception $e) {
            \Log::error("[CLIENT_CONTROLLER] Erreur lors de la mise à jour des données (store) : " . $e->getMessage(), [
                'client' => $client,
                'uid' => $uid,
                'document' => $document
            ]);
            return false;
        }
    }


    /**
     * Récupère, analyse et formate tous les documents d'un client spécifique.
     *
     * @param string $entreprise Le nom du dossier/client
     * @return array La liste formatée des documents
     */
    public static function getAllDocuments(string $entreprise): array
    {
        $basePath = "$entreprise";
        // === RÉCUPÉRATION DE TOUS LES FICHIERS ===
        // Charge la liste complète des fichiers du client depuis le storage
        $files = Storage::disk('public')->allFiles($basePath);

        // === CLASSIFICATION DES FICHIERS PAR TYPE ===
        // Sépare les fichiers en 4 catégories distinctes selon leur chemin
        // Cette approche permet un traitement différencié pour chaque type de document
        $lesDevisFiles = array_filter($files, fn($f) => strpos($f, '/devis/') !== false);
        $lesRapportInterventionFiles = array_filter($files, fn($f) => strpos($f, '/rapport_intervention/') !== false);
        $lesCerfas15497 = array_filter($files, fn($f) => strpos($f, '/cerfa_15497/') !== false);
        // Les fichiers qui ne correspondent à aucune catégorie (fichiers administratifs, formats, etc.)
        $lesDocuments = array_filter($files, fn($f) => strpos($f, '/devis/') === false && strpos($f, '/rapport_intervention/') === false && strpos($f, '/cerfa_15497/') === false);

        // Chargement des règles de format
        $formatPath = 'format.json';
        $formatRules = [];
        if (Storage::disk('public')->exists($formatPath)) {
            $formatContent = Storage::disk('public')->get($formatPath);
            $formatRules = json_decode($formatContent, true);
        }

        // === COMPTEURS POUR LES STATISTIQUES ===
        $nTotal = 0;   // Nombre total de documents
        $nDevis = 0;   // Nombre de devis
        $nBi = 0;      // Nombre de bulletins d'intervention
        $nCerfa = 0;   // Nombre de formulaires CERFA

        // ==========================================
        // 1. TRAITEMENT DES DEVIS
        // ==========================================
        // Groupe les devis par dossier (plusieurs fichiers peuvent être dans le même dossier:
        // devis.pdf, devis_certifie.pdf, devis.json, etc.)
        $devisTraites = [];
        foreach ($lesDevisFiles as $file) {
            $parts = explode('/', $file);
            $devisIndex = array_search('devis', $parts);
            if ($devisIndex === false || !isset($parts[$devisIndex + 1])) continue;

            $folder = $parts[$devisIndex + 1];
            $fileName = end($parts);

            $folderParts = explode('_', $folder);
            $nom = $folderParts[0] ?? $folder;
            $tokenStr = $folderParts[1] ?? null;
            $tokenModel = $tokenStr ? Token::where('token', $tokenStr)->first() : null;

            if (!isset($devisTraites[$folder])) {
                $devisTraites[$folder] = [
                    'nom' => $nom,
                    'token' => $tokenModel->token ?? $tokenStr,
                    'tiers' => $tokenModel->tiers ?? null,
                    'has_normal' => false,
                    'has_certifie' => false,
                    'normal_file' => null,
                    'certifie_file' => null,
                    'normal_last' => null,
                    'certifie_last' => null,
                ];
            }

            if (str_ends_with($fileName, '_certifie.pdf')) {
                $devisTraites[$folder]['has_certifie'] = true;
                $devisTraites[$folder]['certifie_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $devisTraites[$folder]['certifie_last'] = Storage::disk('public')->lastModified($file);
                }
            } elseif (str_ends_with($fileName, '.pdf')) {
                $devisTraites[$folder]['has_normal'] = true;
                $devisTraites[$folder]['normal_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $devisTraites[$folder]['normal_last'] = Storage::disk('public')->lastModified($file);
                }
            }
        }

        // ==========================================
        // 2. TRAITEMENT DES RAPPORTS D'INTERVENTION
        // ==========================================
        $rapportInterventionTraites = [];
        foreach ($lesRapportInterventionFiles as $file) {
            if (str_ends_with($file, 'rapport_intervention/rapport_intervention.pdf')) continue;
            
            $parts = explode('/', $file);
            $rapportIndex = array_search('rapport_intervention', $parts);
            if ($rapportIndex === false || !isset($parts[$rapportIndex + 1])) continue;

            $folder = $parts[$rapportIndex + 1];
            $fileName = end($parts);

            if (!isset($rapportInterventionTraites[$folder])) {
                $rapportInterventionTraites[$folder] = [
                    'uid' => $folder,
                    'has_json' => false,
                    'has_pdf' => false,
                    'json_file' => null,
                    'pdf_file' => null,
                    'json_last' => null,
                    'pdf_last' => null,
                ];
            }

            if (str_ends_with($fileName, '.json')) {
                $rapportInterventionTraites[$folder]['has_json'] = true;
                $rapportInterventionTraites[$folder]['json_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $rapportInterventionTraites[$folder]['json_last'] = Storage::disk('public')->lastModified($file);
                }
            } elseif (str_ends_with($fileName, '.pdf')) {
                $rapportInterventionTraites[$folder]['has_pdf'] = true;
                $rapportInterventionTraites[$folder]['pdf_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $rapportInterventionTraites[$folder]['pdf_last'] = Storage::disk('public')->lastModified($file);
                }
            }

        }

        // ==========================================
        // 3. TRAITEMENT DES CERFAS 15497
        // ==========================================
        $cerfas15497Traites = [];
        foreach ($lesCerfas15497 as $file) {
            if (str_ends_with($file, 'cerfa_15497/cerfa_15497.pdf')) continue;
            
            $parts = explode('/', $file);
            $rapportIndex = array_search('cerfa_15497', $parts);
            if ($rapportIndex === false || !isset($parts[$rapportIndex + 1])) continue;

            $folder = $parts[$rapportIndex + 1];
            $fileName = end($parts);

            if (!isset($cerfas15497Traites[$folder])) {
                $cerfas15497Traites[$folder] = [
                    'uid' => $folder,
                    'has_json' => false,
                    'has_pdf' => false,
                    'json_file' => null,
                    'pdf_file' => null,
                    'json_last' => null,
                    'pdf_last' => null,
                ];
            }

            if (str_ends_with($fileName, '.json')) {
                $cerfas15497Traites[$folder]['has_json'] = true;
                $cerfas15497Traites[$folder]['json_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $cerfas15497Traites[$folder]['json_last'] = Storage::disk('public')->lastModified($file);
                }
            } elseif (str_ends_with($fileName, '.pdf')) {
                $cerfas15497Traites[$folder]['has_pdf'] = true;
                $cerfas15497Traites[$folder]['pdf_file'] = $file;
                if (Storage::disk('public')->exists($file)) {
                    $cerfas15497Traites[$folder]['pdf_last'] = Storage::disk('public')->lastModified($file);
                }
            }

            $nCerfa++;
            $nTotal++;
        }

        \Log::info("[CLIENT_CONTROLLER] DOCUMENTS RECUEILLIS", [
            'client' => $entreprise,
            'nb_devis' => count($devisTraites),
            'nb_bi' => count($rapportInterventionTraites),
            'nb_cerfa' => count($cerfas15497Traites)
        ]);

        // ==========================================
        // 4. CONSTRUCTION DU TABLEAU FINAL DE DOCUMENTS
        // ==========================================
        // Fusionne les 3 types de documents dans un seul tableau avec structure unifiée
        // Chaque document contient: path, status (draft/signed/etc.), date, et métadonnées
        $documents = [];
        
        // === FINALISATION DEVIS ===
        // Convertit les devis traités en structure standardisée pour le frontend
        foreach ($devisTraites as $folder => $d) {
            $status = $d['has_certifie'] ? 'certifie' : '';
            $traitTs = $d['normal_last'] ?? $d['certifie_last'];
            $confTs = $d['certifie_last'] ?? null;

            $dateTrait = $traitTs ? \Carbon\Carbon::createFromTimestamp($traitTs)->toDateTimeString() : null;
            $dateConf = $confTs ? \Carbon\Carbon::createFromTimestamp($confTs)->toDateTimeString() : null;

            $tempsRestants = 0;
            $signable = false;
            
            if ($traitTs) {
                $dateCreation = \Carbon\Carbon::createFromTimestamp($traitTs);
                $joursEcoules = $dateCreation->diffInDays(\Carbon\Carbon::now());
                $tempsRestants = max(0, 30 - $joursEcoules);
                $signable = $tempsRestants > 0;
            }

            $documents['devis/' . $folder] = [
                'path' => 'devis/' . $folder,
                'status' => $status,
                'data' => [
                    "nom" => $d['nom'],
                    "tiers" => $d['tiers'],
                    'token' => $d['token'],
                    "date_traitement" => $dateTrait,
                    "temps_restants" => $tempsRestants,
                    "signable" => $signable,
                    "date_confirmation" => $dateConf,
                    "par" => null
                ]
            ];

            $nDevis++;
            $nTotal++;
        }

        // Finalisation Rapports d'intervention
        foreach ($rapportInterventionTraites as $folder => $r) {
            $status = $r['has_pdf'] ? 'Validé' : 'À traiter';
            $traitTs = $r['json_last'];
            $pdfTs = $r['pdf_last'];

            $dateTrait = $traitTs ? \Carbon\Carbon::createFromTimestamp($traitTs)->toDateTimeString() : null;
            $datePdf = $pdfTs ? \Carbon\Carbon::createFromTimestamp($pdfTs)->toDateTimeString() : null;

            $jsonData = null;
            if ($r['has_json'] && $r['json_file']) {
                try {
                    $jsonContent = Storage::disk('public')->get($r['json_file']);
                    $jsonData = json_decode($jsonContent, true);
                } catch (\Exception $e) {
                    \Log::error("[BI] LECTURE JSON DANS CLIENT_CONTROLLER", ['json_file' => $r['json_file']]);
                }
            }

            $documents['rapport_intervention/' . $folder] = [
                'path' => 'rapport_intervention/' . $folder,
                'status' => $status,
                'token_rapport' => null, 
                'data' => [
                    "nom" => $r['uid'],
                    "tiers" => $jsonData['nom_client'] ?? null,
                    "intervenant" => $jsonData['intervenant'] ?? null,
                    "description" => $jsonData['description'] ?? null,
                    "date_traitement" => $dateTrait,
                    "date_pdf" => $datePdf,
                    "par" => null
                ]
            ];

            if ($r['json_file']) {
                $pathToken = "app/public/" . $r['json_file'];
                $tokenRapport = TokenLinksRapport::where('paths', $pathToken)->first();
                if ($tokenRapport) {
                    $documents['rapport_intervention/' . $folder]["token_rapport"] = $tokenRapport->token;
                }
            }

            $nBi++;
            $nTotal++;
        }

        // Finalisation Cerfas
        foreach ($cerfas15497Traites as $folder => $r) {
            $status = $r['has_pdf'] ? 'Validé' : 'À traiter';
            $traitTs = $r['json_last'];
            $pdfTs = $r['pdf_last'];

            $dateTrait = $traitTs ? \Carbon\Carbon::createFromTimestamp($traitTs)->toDateTimeString() : null;
            $datePdf = $pdfTs ? \Carbon\Carbon::createFromTimestamp($pdfTs)->toDateTimeString() : null;

            $jsonData = null;
            if ($r['has_json'] && $r['json_file']) {
                try {
                    $jsonContent = Storage::disk('public')->get($r['json_file']);
                    $jsonData = json_decode($jsonContent, true);
                } catch (\Exception $e) {}
            }

            $documents['cerfa_15497/' . $folder] = [
                'path' => 'cerfa_15497/' . $folder,
                'status' => $status,
                'token_rapport' => null,
                'data' => [
                    "nom" => $r['uid'],
                    "tiers" => $jsonData['nom_client'] ?? null,
                    "operateur" => $jsonData['operateur'] ?? null,
                    "detenteur" => $jsonData['detenteur'] ?? null,
                    "nature_intervention" => $jsonData['nature_intervention'] ?? null,
                    "date_traitement" => $dateTrait,
                    "date_pdf" => $datePdf,
                    "par" => null
                ]
            ];

            if ($r['json_file']) {
                $pathToken = "app/public/" . $r['json_file'];
                $tokenRapport = TokenLinksRapport::where('paths', $pathToken)->first();
                if ($tokenRapport) {
                    $documents['cerfa_15497/' . $folder]["token_rapport"] = $tokenRapport->token;
                }
            }

            $nCerfa++;
            $nTotal++;
        }

        // ==========================================
        // 5. TRAITEMENT DES AUTRES DOCUMENTS
        // ==========================================

        Log::info("Documetns : ", ['documents' => $documents]);

        foreach ($lesDocuments as $file) {
            $relativePath = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $file);
            $dirPath = dirname($relativePath);
            $docType = explode('/', $relativePath)[0];

            if ($dirPath === '.' || $dirPath === $basePath) {
                continue;
            }

            if (!isset($documents[$dirPath])) {
                $documents[$dirPath] = [
                    'path' => $dirPath,
                    'token_rapport' => null,
                    'status' => 'À traiter',
                    'data' => null
                ];
            }

            if (str_ends_with($file, '.pdf')) {
                $documents[$dirPath]['status'] = 'Validé';
            }

            if (str_ends_with($file, '.json')) {
                try {
                    $jsonContent = Storage::disk('public')->get($file);
                    $jsonData = json_decode($jsonContent, true);

                    $pathToken = "app/public/" . $file;
                    $dataToken = TokenLinksRapport::where('paths', $pathToken)->first();
                    $documents[$dirPath]["token_rapport"] = $dataToken["token"] ?? null;

                    if (isset($formatRules[$docType])) {
                        $rules = $formatRules[$docType];
                        $formattedData = [];

                        foreach ($rules as $key => $rule) {
                            if (is_array($rule)) {
                                $formattedData[$key] = implode(' ', array_filter(array_map(fn($r) => $jsonData[$r] ?? '', $rule)));
                            } elseif (is_string($rule)) {
                                $formattedData[$key] = $jsonData[$rule] ?? null;
                            } else {
                                $formattedData[$key] = null;
                            }
                        }

                        $documents[$dirPath]['data'] = $formattedData;
                    }
                } catch (\Exception $e) {
                    $documents[$dirPath]['data'] = ['error' => 'Fichier JSON illisible'];
                }
            }
        }

        // Ajouter nombre de documents total et chaque type dans une balise metadata.

        // === FILTRAGE FINAL ===
        // Supprime les documents mal formés (sans chemin valide) et réindexe le tableau
        // array_values() réinitialise les clés numériques pour éviter les trous d'indices
        return array_values(array_filter($documents, fn($doc) => strpos($doc['path'], '/') !== false));
    }


    /**
     * Récupère les options de configuration pour les Bons d'Intervention d'un client.
     * * @param string $client Le nom du dossier client
     * @return array Retourne un tableau multidimensionnel (options par catégories)
     */
    /**
     * Récupère les options BI et les force en tableau indexé (0, 1, 2, 3)
     */
    public static function getOptionsBI(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Options_BI.json";

        if (!Storage::disk('public')->exists($fileName)) {
            return [];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            // array_values garantit que même si le JSON est {"a": [], "b": []},
            // le résultat sera [[], []] (utilisable avec [0], [1]...)
            return array_values($data);

        } catch (\Exception $e) {
            \Log::error("Erreur Options_BI : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprime physiquement le dossier d'un document et nettoie la base de données.
     * * @param string $client Nom du client
     * @param string $document Type de document
     * @param string $uid Identifiant unique
     * @param TokenLinksRapport|null $tokenRecord L'instance du modèle à supprimer
     * @return bool
     */
    public static function delete($client, $document, $uid, $tokenRecord = null): bool
    {
        try {
            // Définir le chemin relatif du dossier (pour Storage::disk('public'))
            $relativeFolderPath = "{$client}/{$document}/{$uid}";

            // Suppression physique du dossier et de tout son contenu (JSON, PDF, Images)
            if (Storage::disk('public')->exists($relativeFolderPath)) {
                $deleted = Storage::disk('public')->deleteDirectory($relativeFolderPath);
                if (!$deleted) {
                    \Log::error("[CLIENT_CONTROLLER] Impossible de supprimer le dossier", ['path' => $relativeFolderPath]);
                    return false;
                }
            }

            // Nettoyage de la base de données
            if ($tokenRecord) {
                $tokenRecord->delete();
            }

            return true;
        } catch (\Throwable $e) {
            \Log::error("[CLIENT_CONTROLLER] Erreur lors de la suppression : " . $e->getMessage());
            return false;
        }
    }
}
