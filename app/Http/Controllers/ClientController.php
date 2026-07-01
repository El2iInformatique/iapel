<?php

namespace App\Http\Controllers;

use App\Models\TokenLinks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

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

    public static function createCerfaConfigFile(string $client): bool 
    {
        $optionFile = "{$client}/Config_Cerfa.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($optionFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "numeroAttestationCapacite" => "Un numéro d'attestation de capacité",
            "identificationControle" => "Un identificateur de controle",
            
            "nom" => $client,
            "adresse" => "",
            "siret" => "",

            "OperateurSignataireQualiter" => "Technicien",
            "controleMaterielDate" => now()->format("Y-m-d"),

            "OperateurSignataireNom" => ""
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $optionFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[CONFIG_CERFA] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée le fichier documents.json avec une structure par défaut s'il n'existe pas.
     * * @param string $client Le nom du client (dossier)
     * @return bool True si créé avec succès, False s'il existe déjà ou en cas d'erreur
     */
    public static function createDocumentsFile(string $client): bool 
    {
        $documentsFile = "{$client}/documents.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($documentsFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "documents" => [
                [
                    "code" => "rapport_intervention",
                    "libelle" => "Rapport d'intervention"
                ]
            ]
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $documentsFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[DOCUMENTS_FILE] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée le fichier documents.json avec une structure dynamique.
     * * @param string $client Le nom du client (dossier)
     * @return bool True si créé avec succès, False s'il existe déjà ou en cas d'erreur
     */
    public static function createDocumentsFileSpecific(string $client, Request $request): bool
    {
        $documentsFile = "{$client}/documents.json";
        $listeDoc = $request->input('documents');

        $data = [
            "documents" => []
        ];

        foreach ($listeDoc as $doc) {

            $data["documents"][] = [
                "code" => $doc,
                "libelle" => ucfirst(str_replace('_', ' ', $doc))
            ];
        }

        try {
            $stored = Storage::disk('public')->put(
                $documentsFile,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored;

        } catch (\Exception $e) {
            \Log::error("[DOCUMENTS_FILE] Erreur de création pour le client {$client}: " . $e->getMessage());
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
                    if (!ClientController::createCerfaConfigFile($client)) {
                        Log::warning("Erreur lors de la création du fichier de configuration des cerfas : " . $optionFile);
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

    /**
     * Create client folder on server.
     */

    public static function createClientFolder($client): bool
    {
        if (!$client) {
            return false;
        }

        try {
            if (!Storage::disk('public')->exists($client)) {
                Storage::disk('public')->makeDirectory($client);
            }
            /** Creation fichier documents.json */
            $documentsFile = "{$client}/documents.json";
            if (!Storage::disk('public')->exists($documentsFile)) {
                if (!ClientController::createDocumentsFile($client)) {
                    Log::warning("Erreur lors de la création du fichier de configuration des documents : " . $documentsFile);
                }
            }
            /* VOIR SI D'AUTRES DOCUMENTS SONT NECESSAIRES A RAJOUTER A LA CREATION DU DOSSIER CLIENT */

        } catch (\Throwable $e) {
            Log::error("Erreur création dossier client: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Create specific folder on server for a client.
     */
    public static function createSpecificFolder(Request $request, $client, $document): bool
    {
        if (!$client && !$document) {
            return false;
        }

        $path = "{$client}/{$document}";
        
        try {
            /** Création du repertoire document */
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }
            /** Upload du fichier, s'il y en a un */
            if ($request->hasFile('file')) {
                if (!ClientController::uploadFile($request, $client, $document)) {
                    Log::warning("Erreur lors de l\'upload du fichier: ");
                }
            }

        } catch (\Throwable $e) {
            Log::error("Erreur création du dossier " . "$document" . " : " . $e->getMessage());
            return false;
        }

        return true;
    }

    public static function uploadFile(Request $request, $client, $document)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if (!$file->isValid()) {
                return response()->json(['error' => 'Invalid file'], 400);
            }

            $path = "{$client}/{$document}";

            $file->storeAs($path, $file->getClientOriginalName(), 'public');

            return response()->json([
                'success' => true,
                'path' => $path
            ]);
        }
    }

    /**
     * Create full folder on server for a client with specific doc needed.
     */
    public static function CreateFullFolder($client, $document, Request $request): bool
    {
        if (!$client && !$document) {
            return false;
        }

        try {
            if ($client) {
                if (!ClientController::createClientFolder($client)) {
                    Log::warning("Erreur création dossier client: ");
                }
                if (!ClientController::createSpecificFolder($request, $client, $document)) {
                    Log::warning("Erreur création du dossier " . "$document" . " : ");
                }
            }
        } catch (\Throwable $e) {
            Log::error("Erreur création du dossier " . "$document" . " : " . $e->getMessage());
            return false;
        }

        return true;
    }

    public static function createDevis($organisation_id, $document, $devis_id, array $validated = null) {
        if (empty($organisation_id) || empty($document) || empty($devis_id) || !$validated) {
            return false;
        }


        // Préparation du chemin et des données
        $relativeFolder = "{$organisation_id}/{$document}/{$devis_id}";
        $relativeFilePath = "{$relativeFolder}/{$devis_id}.json";

        $jsonData = [
            'dataToken' => [
                'organisation_id'   => $organisation_id,
                'document' => $document,
                'devis_id'      => $devis_id,
            ]
        ];


        try {
            $validated["used"] = false;
            // On fusionne les champs extra (ceux qui ne sont pas dans le bloc dataToken)
            $extraFields = array_diff_key($validated, array_flip(['devis_id', 'organisation_id', 'document']));
            $jsonData = array_merge($jsonData, $extraFields);


            $stored = Storage::disk('public')->put(
                    $relativeFilePath, 
                    json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            Log::info("stored : ", ["stored" => $stored]);

            // On s'assure que le fichier a bien été écrit sur le disque
            if (!$stored) {
                throw new \Exception("Échec de l'écriture du fichier JSON sur le disque.");
            }

            return true;


        } catch (\Exception $e) {
            // Log de l'erreur pour le debug
            \Log::error("Erreur lors de la création du fichier JSON: " . $e->getMessage());
            return false;
        }

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
                    'operateur', 'detenteur', 'identification', 
                    'denomination', 'charge', 'tonnage', 'nature_intervention', 'autre_valeur', 
                    'detection_fuites', 'hcfc', 'hfc_pfc', 'hfo', 
                    'equipement_sans_detection', 'equipement_avec_detection', 
                    'constat_fuites', 'localisation_fuite_1', 'reparation_fuite_1', 
                    'localisation_fuite_2', 'reparation_fuite_2', 'localisation_fuite_3', 
                    'reparation_fuite_3', 'quantite_chargee_totale', 'quantite_chargee_A', 
                    'fluide_A', 'quantite_chargee_B', 'quantite_chargee_C', 'quantite_recuperee_totale', 
                    'quantite_recuperee_D', 'identification_controle', 'BSFF', 'quantite_recuperee_E', 'identification_E', 
                    'fluide_non_inflammable', 'autre_fluide_non_inflammable', 'fluide_inflammable', 
                    'autre_fluide_inflammable', 'installation_destination_fluide', 'observations',
                    'nom_signataire_operateur', 'qualite_signataire_operateur', 
                    'nom_signataire_detenteur', 'qualite_signataire_detenteur', 
                    "detenteur_nom", "detenteur_adresse", "detenteur_siret"
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
        // === 1. RÉCUPÉRATION ET GROUPEMENT DES FICHIERS ===
        $files = Storage::disk('public')->allFiles($entreprise);
        $documentsTraites = [];

        foreach ($files as $file) {
            $relativePath = preg_replace('#^' . preg_quote($entreprise, '#') . '/?#', '', $file);
            $parts = explode('/', $relativePath);

            // Ignorer les fichiers hors arborescence type/dossier/fichier
            if (count($parts) < 3) continue;

            $type = $parts[0];       // ex: devis, rapport_intervention, cerfa_15497
            $folder = $parts[1];     // ex: DEVIS_EXEMPLE
            $fileName = end($parts); 

            // Ignorer les PDF vides de template
            if ($fileName === "$type.pdf") continue;

            $docKey = "$type/$folder"; 

            if (!isset($documentsTraites[$docKey])) {
                $documentsTraites[$docKey] = [
                    'path' => $docKey,
                    'type' => $type,
                    'folder' => $folder,
                    'json_file' => null,
                    'pdf_file' => null,
                    'certifie_file' => null,
                    'json_last' => null,
                    'pdf_last' => null,
                    'certifie_last' => null,
                ];
            }

            $lastModified = Storage::disk('public')->lastModified($file);

            if (str_ends_with($fileName, '.json')) {
                $documentsTraites[$docKey]['json_file'] = $file;
                $documentsTraites[$docKey]['json_last'] = $lastModified;
            } elseif (str_ends_with($fileName, '_certifie.pdf')) {
                $documentsTraites[$docKey]['certifie_file'] = $file;
                $documentsTraites[$docKey]['certifie_last'] = $lastModified;
            } elseif (str_ends_with($fileName, '.pdf')) {
                $documentsTraites[$docKey]['pdf_file'] = $file;
                $documentsTraites[$docKey]['pdf_last'] = $lastModified;
            }
        }

        // === 2. LECTURE DES JSON, RÉCUPÉRATION DES TOKENS ET FORMATAGE ===
        $documentsFinals = [];

        foreach ($documentsTraites as $doc) {
            $type = $doc['type'];
            $folder = $doc['folder'];
            
            $jsonData = [];
            $token = null;

            if ($doc['json_file']) {
                try {
                    $jsonContent = Storage::disk('public')->get($doc['json_file']);
                    $jsonData = json_decode($jsonContent, true) ?? [];

                    // Récupération robuste du token en BDD avec LIKE
                    $tokenRecord = TokenLinks::where('paths', 'LIKE', '%' . ltrim($doc['json_file'], '/'))->latest('created_at')->first();
                    
                    if (!$tokenRecord) {
                        $tokenRecord = TokenLinks::where('paths', "app/public/" . ltrim($doc['json_file'], '/'))->latest('created_at')->first();
                    }

                    $token = $tokenRecord->token ?? null;

                } catch (\Exception $e) {
                    Log::error("[LIST_SAVED_DOCS] Erreur JSON/DB", ['file' => $doc['json_file'], 'error' => $e->getMessage()]);
                }
            }

            /*
            * Structure de base commune à tous les types de documents, 
            * avec des champs spécifiques ajoutés ensuite selon le type
            */
            $docEntry = [
                'path' => $doc['path'],
                'status' => 'À traiter',
                'type' => $type, // Ex: devis, rapport_intervention, cerfa_15497
                'token_rapport' => $token, // Legacy, à supprimer progressivement au profit de token_rapport
                'token' => $token, // Token global, à privilégier désormais pour tous les types de documents
                'data' => []
            ];
            
            $dateJson = $doc['json_last'] ? Carbon::createFromTimestamp($doc['json_last'])->toDateTimeString() : null;
            $datePdf = $doc['pdf_last'] ? Carbon::createFromTimestamp($doc['pdf_last'])->toDateTimeString() : null;

            // --- MAPPING DEVIS ---
            if ($type === 'devis') {
                $docEntry['status'] = $doc['certifie_file'] ? 'certifie' : '';
                $traitTs = $doc['pdf_last'] ?? $doc['certifie_last'];
                $confTs = $doc['certifie_last'];

                $tempsRestants = 0;
                $signable = false;
                
                if ($traitTs) {
                    $joursEcoules = Carbon::createFromTimestamp($traitTs)->floatDiffInDays(Carbon::now());
                    $tempsRestants = max(0, 30 - $joursEcoules);
                    $signable = $tempsRestants > 0;
                }

                // On retire le token de la racine pour les devis
                $docEntry['token_rapport'] = null; 
                
                $docEntry['data'] = [
                    "nom" => $jsonData['titre'] ?? $folder,
                    "tiers" => $jsonData['tiers'] ?? null,
                    "token" => $token, // Legacy, à supprimer progressivement au profit de token
                    "date_traitement" => $traitTs ? Carbon::createFromTimestamp($traitTs)->toDateTimeString() : null,
                    "temps_restants" => $tempsRestants,
                    "signable" => $signable,
                    "date_confirmation" => $confTs ? Carbon::createFromTimestamp($confTs)->toDateTimeString() : null,
                    "par" => null
                ];
            } 
            // --- MAPPING RAPPORT INTERVENTION ---
            elseif ($type === 'rapport_intervention') {
                $docEntry['status'] = $doc['pdf_file'] ? 'Validé' : 'À traiter';
                
                $docEntry['data'] = [
                    "nom" => $jsonData['dataToken']['uid'] ?? $folder,
                    "tiers" => $jsonData['nom_client'] ?? null,
                    "intervenant" => $jsonData['intervenant'] ?? null,
                    "description" => $jsonData['description'] ?? null,
                    "date_traitement" => $dateJson,
                    "date_pdf" => $datePdf,
                    "par" => null
                ];
            } 
            // --- MAPPING CERFA ---
            elseif ($type === 'cerfa_15497') {
                $docEntry['status'] = $doc['pdf_file'] ? 'Validé' : 'À traiter';
                $configCerfa = ClientController::getConfigCerfa($jsonData['dataToken']['client']);
                
                $docEntry['data'] = [
                    "nom" => $jsonData['dataToken']['uid'] ?? $folder,
                    "tiers" => $jsonData['dataToken']['client'] ?? null, 
                    "operateur" => $jsonData['operateur'] ?? null,
                    "nomOperateur" => $configCerfa['nom'] ?? null,
                    "detenteur" => $jsonData['detenteur'] ?? null,
                    "nature_intervention" => $jsonData['nature_intervention'] ?? null,
                    "date_traitement" => $dateJson,
                    "date_pdf" => $datePdf,
                    "par" => null
                ];
            }

            $documentsFinals[] = $docEntry;
        }

        return array_values($documentsFinals);
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

    public static function getOptionsBIAsMap(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Options_BI.json";

        if (!Storage::disk('public')->exists($fileName)) {
            // On retourne la structure par défaut si le fichier n'existe pas encore
            return [
                "Constat" => [],
                "Verification" => [],
                "NotesParticuliere" => [],
                "PointVigilance" => []
            ];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            // On s'assure de bien retourner un tableau associatif
            return is_array($data) ? $data : [];

        } catch (\Exception $e) {
            \Log::error("Erreur getOptionsBIAsMap : " . $e->getMessage());
            return [];
        }
    }


    public static function getConfigCerfa(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Config_Cerfa.json";

        if (!Storage::disk('public')->exists($fileName)) {
            return [];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            return $data;

        } catch (\Exception $e) {
            \Log::error("Erreur Config_Cerfa : " . $e->getMessage());
            return [];
        }
    }

    public static function getDocumentCodes(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/documents.json";

        if (!Storage::disk('public')->exists($fileName)) {
            return [];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            $result = [];

            foreach ($data['documents'] ?? [] as $doc) {
                if (!isset($doc['code'])) {
                    continue;
                }

                $code = $doc['code'];

                $path = "{$client}/{$code}/{$code}.pdf";

                $result[$code] = [
                    'code' => $code,
                    'file' => Storage::disk('public')->exists($path)
                        ? $path
                        : null,
                ];
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error("Erreur Documents.json : " . $e->getMessage());
            return [];
        }
    }

    public static function modeleExists(string $client, string $document): bool
    {
        return Storage::disk('public')
            ->exists("{$client}/{$document}/{$document}.pdf");

    }

    public static function updateOptionsBI(string $client, array $newConfig): bool
    {
        if (empty($client) || empty($newConfig)) {
            return false;
        }

        $fileName = "{$client}/Options_BI.json";
        $existingConfig = self::getOptionsBIAsMap($client);

        // array_merge écrase les anciennes valeurs par les nouvelles
        $updatedConfig = array_merge($existingConfig, $newConfig);

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($updatedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {
            \Log::error("Erreur Options_BI : " . $e->getMessage());
            return false;
        }
    }


    public static function updateConfigCerfa(string $client, array $newConfig): bool
    {
        if (empty($client) || empty($newConfig)) {
            return false;
        }

        $fileName = "{$client}/Config_Cerfa.json";
        $existingConfig = self::getConfigCerfa($client);

        // array_merge va écraser les valeurs de $existingConfig par celles de $newConfig
        // uniquement pour les clés qui sont présentes dans $newConfig.
        // C'est beaucoup plus propre et dynamique !
        $updatedConfig = array_merge($existingConfig, $newConfig);

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($updatedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour du Config_Cerfa pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }

    public static function updateDocumentsFile(string $client, array $documents): bool
    {
        if (empty($client)) {
            return false;
        }

        $fileName = "{$client}/documents.json";

        $data = [
            "documents" => array_values($documents)
        ];

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {

            \Log::error("Erreur replace documents {$client}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Supprime physiquement le dossier d'un document et nettoie la base de données.
     * * @param string $client Nom du client
     * @param string $document Type de document
     * @param string $uid Identifiant unique
     * @param TokenLinks|null $tokenRecord L'instance du modèle à supprimer
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

    public function storeDoc(Request $request)
    {
        $client = $request->input('client');
        ClientController::createClientFolder($client);

        $documents = $request->input('documents', []);
        ClientController::updateDocumentsFile($client, $documents);

        foreach ($request->input('documents', []) as $index => $doc) {

            $file = $request->file("documents.$index.file");

            if (!$file) {
                Log::error("Fichier manquant", ['index' => $index]);
                continue;
            }

            $file->storeAs(
                "{$client}/{$doc['code']}",
                "{$doc['code']}.pdf",
                'public'
            );
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
