<?php

namespace App\Http\Controllers;

use App\Models\layou_client;
use App\Models\Token;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class BiController extends Controller
{

    // Évite les répétitions // récupère les données des fichiers JSON via le token
    public function getJSONDataFromToken($dataToken){


        
        
        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            abort(404, 'Fichier JSON introuvable');
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        return $data;
    }

    // Renvoie en JSON le document "documents.json"
    public function getDocuments($client)
    {
        // Récupération du fichier JSON
        $filePath = storage_path('app/public/' . $client . '/documents.json');

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        // Lire et décoder le JSON
        $json = file_get_contents($filePath);
        $data = json_decode($json, true); // true => convertir en tableau associatif

        return response()->json($data);
    }

    // Renvoie un téléchargement d'un document demandé via un token
    public function download($token)
    {
        // Récupère les données du token
        $dataToken = Token::where('token', $token)->get()->first();

        // Appel de la fonction qui récupère les données du token
        $data = self::getJSONDataFromToken($dataToken);

        // Créer les données utilisées après
        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        // Créer le chemin vers le fichier
        $filePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        return response()->download($filePath, "{$uid}.pdf");
    }

    // Renvoie si un document existe ou pas
    public function check($token, Request $request)
    {
        // Récupère les données du token
        $dataToken = Token::where('token', $token)->get()->first();

        // Appel de la fonction qui récupère les données du token
        $data = self::getJSONDataFromToken($dataToken);

        // Créer les données utilisées après
        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        // Créer les chemins vers les fichiers
        $jsonPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json');
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        return response()->json([
            'json_exists' => file_exists($jsonPath),
            'pdf_exists' => file_exists($pdfPath)
        ]);
    }

    // Renvoie une vue via le type du document : rapport d'intervention / cerfa
    public function show($token)
    {
        $dataToken = Token::where('token', $token)->get()->first();

        // Appel de la fonction qui récupère les données du token
        $data = self::getJSONDataFromToken($dataToken);

        // Créer les données utilisées après
        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        
        if (str_starts_with($document, 'cerfa_15497_')) { // Test si le document est un cerfa 15497 pour afficher la vue du cerfa_15497
            return view('cerfa_15497', compact('data', 'token', 'uid', 'client', 'document'));
        }

        else if (str_starts_with($document, 'cerfa')) { // Test si le document est un cerfa pour afficher la vue du cerfa
            return view($document, compact('data', 'token', 'uid', 'client', 'document'));
        } 
        
        else { // Sinon renvoie la vue des bi donc des rapports d'intervention

            // Recupere le layout du client
            $client_layout = layou_client::where('nom_client', $client)->first();
            Log::info("Token : " . $token . ", client : " . $client . ", document" . $document . ", uid : " . $uid);

            // Test si le layout existe
            if (!$client_layout) {
                return view('bi', compact('data', 'token', 'uid', 'client', 'document'));
            }
            else {
                return view('bi', compact('data', 'token', 'uid', 'client', 'document', 'client_layout'));
            }

        }
    }

    // Enregistre les données dans un fichier JSON par rapport à son nom
    public function submit(Request $request, $token)
    {
        $dataToken = Token::where('token', $token)->get()->first();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier JSON existe
        if (!file_exists($filePath)) {
            abort(404, "Fichier JSON introuvable : $filePath");
        }

        // Lire le contenu existant
        $data = json_decode(file_get_contents($filePath), true);

        $document = $data['dataToken']['document'];
        $client = $data['dataToken']['client'];
        $uid = $data['dataToken']['uid'];

        if ($document == 'rapport_intervention') {
            $biPath = $client . '/' . $document . '/' . $uid;
            $data['intervention_realisable'] = $request->input('intervention_realisable');
            $data['equipier'] = $request->input('equipier');
            $data['compte_rendu'] = $request->input('compte_rendu');
            $data['materiel'] = $request->input('materiel');
            $data['intervention_suite'] = $request->input('intervention_suite');
            $data['materiel'] = $request->input('materiel');
            $data['facturable'] = $request->input('facturable');
            $data['terminee'] = $request->input('terminee');
            $data['absent'] = $request->input('absent');
            $data['fait-le'] = $request->input('fait-le');
            $data['devis_a_faire'] = $request->input('devis_a_faire');

            if ($request->hasFile('photo_avant')) {
                $imagePath = $request->file('photo_avant')->store($biPath, 'public');
                $data['photo_avant'] = $imagePath;
            }
            if ($request->hasFile('photo_apres')) {
                $imagePath = $request->file('photo_apres')->store($biPath, 'public');
                $data['photo_apres'] = $imagePath;
            }

            $data['complements'] = [];
            $comments = $request->input('comments');
            $images = $request->input('images');
            if (isset($images)) {
                foreach ($images as $index => $image) {
                    // Stocker chaque image et son commentaire dans un tableau
                    $data['complements'][] = [
                        'image' => $biPath . '/' . $image,
                        'comment' => $comments[$index] ?? '', // Associer le bon commentaire
                    ];
                }
            }

            // Ajout des complement du client dans le json
            $data['complement_client'] = [];

            foreach ($request->all() as $key => $value) {

                if ($request->hasfile($key) && str_starts_with($key, 'item')) {
                    $question = $request->input('question-' . $key); // Recupere la question qui se trouve dans un input (hidden)

                    $imagePath = $request->file($key)->store($biPath, 'public');
                    $data['complement_client'][] = [
                        'value' => $imagePath,
                        'question' => $question,
                        'type' => 'img'
                    ];
                }
                else if (str_starts_with($key, 'item')) {
                    $question = $request->input('question-' . $key); // Recupere la question qui se trouve dans un input (hidden)

                    $data['complement_client'][] = [
                        'value' => $value,
                        'question' => $question,
                        'type' => 'text'
                    ];
                }
            }

            if ($request->input('signature')) {
                $data['signature'] = $request->input('signature');
            }

        } else if (str_starts_with($document, 'cerfa_15497')) {
            // Mettre à jour les données avec les nouvelles valeurs du formulaire
            $data['operateur'] = $request->input('operateur');
            $data['detenteur'] = $request->input('detenteur');

            $data['numero_attestation_capacite'] = $request->input('numero_attestation_capacite');

            $data['identification'] = $request->input('identification');
            $data['denomination'] = $request->input('denomination');
            $data['charge'] = $request->input('charge');
            $data['tonnage'] = $request->input('tonnage');

            $data['nature_intervention'] = $request->input('nature_intervention');
            $data['autre_valeur'] = $request->input('autre_valeur');

            $data['identification_controle'] = $request->input('identification_controle');
            $data['date_controle'] = $request->input('date_controle');

            $data['detection_fuites'] = $request->input('detection_fuites');

            $data['hcfc'] = $request->input('hcfc');
            $data['hfc_pfc'] = $request->input('hfc_pfc');
            $data['hfo'] = $request->input('hfo');

            $data['equipement_sans_detection'] = $request->input('equipement_sans_detection');
            $data['equipement_avec_detection'] = $request->input('equipement_avec_detection');

            $data['constat_fuites'] = $request->input('constat_fuites');

            $data['localisation_fuite_1'] = $request->input('localisation_fuite_1');
            $data['reparation_fuite_1'] = $request->input('reparation_fuite_1');
            $data['localisation_fuite_2'] = $request->input('localisation_fuite_2');
            $data['reparation_fuite_2'] = $request->input('reparation_fuite_2');
            $data['localisation_fuite_3'] = $request->input('localisation_fuite_3');
            $data['reparation_fuite_3'] = $request->input('reparation_fuite_3');

            $data['quantite_chargee_totale'] = $request->input('quantite_chargee_totale');
            $data['quantite_chargee_A'] = $request->input('quantite_chargee_A');
            $data['fluide_A'] = $request->input('fluide_A');
            $data['quantite_chargee_B'] = $request->input('quantite_chargee_B');
            $data['quantite_chargee_C'] = $request->input('quantite_chargee_C');

            $data['quantite_recuperee_totale'] = $request->input('quantite_recuperee_totale');
            $data['quantite_recuperee_D'] = $request->input('quantite_recuperee_D');
            $data['BSFF'] = $request->input('BSFF');
            $data['quantite_recuperee_E'] = $request->input('quantite_recuperee_E');
            $data['identification_E'] = $request->input('identification_E');

            $data['fluide_non_inflammable'] = $request->input('fluide_non_inflammable');
            $data['autre_fluide_non_inflammable'] = $request->input('autre_fluide_non_inflammable');
            $data['fluide_inflammable'] = $request->input('fluide_inflammable');
            $data['autre_fluide_inflammable'] = $request->input('autre_fluide_inflammable');

            $data['installation_destination_fluide'] = $request->input('installation_destination_fluide');

            $data['observations'] = $request->input('observations');

            // Gestion de la photo
            /*
            if ($request->hasFile('photo')) {
                $imagePath = $request->file('photo')->store('images', 'public');
                $data['photo'] = $imagePath;
            }
            // Sauvegarde du commentaire
            $data['commentaire'] = $request->input('commentaire');
            */

            $data['nom_signataire_operateur'] = $request->input('nom_signataire_operateur');
            $data['qualite_signataire_operateur'] = $request->input('qualite_signataire_operateur');
            $data['date_signature_operateur'] = date('d/m/Y');
            if ($request->input('signature-operateur')) {
                $data['signature-operateur'] = $request->input('signature-operateur');
            } else {
                $data['signature-operateur'] = null;
            }

            $data['nom_signataire_detenteur'] = $request->input('nom_signataire_detenteur');
            $data['qualite_signataire_detenteur'] = $request->input('qualite_signataire_detenteur');
            $data['date_signature_detenteur'] = date('d/m/Y');
            if ($request->input('signature-detenteur')) {
                $data['signature-detenteur'] = $request->input('signature-detenteur');
            } else {
                $data['signature-detenteur'] = null;
            }
        } else if ($document == 'cerfa_13948-03') {
            $data['nom'] = $request->input('nom');
            $data['prenom'] = $request->input('prenom');
            $data['adresse'] = $request->input('adresse');
            $data['code_postal'] = $request->input('code_postal');
            $data['commune'] = $request->input('commune');

            $data['nature_locaux_type'] = $request->input('nature_locaux_type');
            $data['nature_locaux_type_autre_valeur'] = $request->input('nature_locaux_type_autre_valeur');
            $data['nature_locaux_affectation'] = $request->input('nature_locaux_affectation');
            $data['milliemes'] = $request->input('milliemes');
            $data['adresse_travaux'] = $request->input('adresse_travaux');
            $data['code_postal_travaux'] = $request->input('code_postal_travaux');
            $data['commune_travaux'] = $request->input('commune_travaux');
            $data['nature_locaux_status'] = $request->input('nature_locaux_status');
            $data['nature_locaux_status_autre_valeur'] = $request->input('nature_locaux_status_autre_valeur');
            $data['travaux'] = $request->input('travaux');
            $data['travaux_2_details'] = $request->input('travaux_2_details');

            $data['fait_a'] = $request->input('fait_a');
            $data['fait_le'] = $request->input('fait_le');

            if ($request->input('signature')) {
                $data['signature'] = $request->input('signature');
            } else {
                $data['signature'] = null;
            }
        }

        // Sauvegarder les nouvelles données dans le fichier JSON
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Rediriger avec un message de succès
        return redirect()->route('pdf.view', ['token' => $token]);
    }



    public function listSavedDocs($entreprise, Request $request): JsonResponse
{
    try {
        $basePath = "$entreprise";

        // Récupère tous les fichiers de l'entreprise / client
        $files = Storage::disk('public')->allFiles($basePath);

        // Récupère tous les devis de l'entreprise / client grâce au fichier récupéré avant
        $lesDevis = array_filter($files, function ($file) {
            return explode('/', $file)[1] === 'devis';
        });

        // Récupère tous les autre documents de l'entreprise / client grâce au fichier récupéré avant
        $lesDocuments = array_filter($files, function ($file) {
            return explode('/', $file)[1] !== 'devis';
        });

        \Log::info($lesDevis);

        $formatPath = 'format.json';
        $formatRules = [];
        if (Storage::disk('public')->exists($formatPath)) {
            $formatContent = Storage::disk('public')->get($formatPath);
            $formatRules = json_decode($formatContent, true);
        }

        // Format de JSON pour qu'il soit lisible côté client et rajoute le token du document
        $documents = [];
        $devisTraites = [];
        
        foreach ($lesDevis as $file) {
            $fileName = explode('/', $file)[3];
            $exploded = explode('_', $fileName);
            $devisName = explode('.', $exploded[0] . '_' . $exploded[1])[0];

            // Récupération du token avec vérification
            $tokenValue = explode('.', $exploded[1])[0];
            $token = Token::where('token', $tokenValue)->first();
            
            // Si le token n'existe pas, on log l'erreur et on passe au suivant
            if (!$token) {
                \Log::warning("Token non trouvé pour le devis: $fileName avec token: $tokenValue");
                continue; // On passe au devis suivant
            }

            if (!isset($devisTraites[$devisName])) {
                if (count($exploded) === 2) {
                    $devisTraites[$devisName] = [
                        'nom' => explode('_', $devisName)[0],
                        'status' => '',
                        'tiers' => $token->tiers,
                        'token' => $token->token,
                    ];
                } elseif (count($exploded) === 3) {
                    $devisTraites[$devisName] = [
                        'nom' => $devisName,
                        'status' => 'certifie',
                        'tiers' => $token->tiers,
                        'token' => $token->token,
                    ];
                }
            } else {
                if (count($exploded) === 3) {
                    $devisTraites[$devisName]['status'] = 'certifie';
                }
            }
        }
        
        \Log::info('Devis : ' . json_encode($devisTraites));
        
        foreach ($devisTraites as $devis) {
            $devisNom = 'devis/' . $devis['nom'] . '_' . $devis['token'] . '_' . ($devis['status'] === 'certifie' ? 'certifie' : '');
            $documents[$devisNom] = [
                'path' => $devisNom,
                'status' => $devis['status'],
                'data' => [
                    "nom" => $devis['nom'],
                    "tiers" => $devis['tiers'],
                    'token' => $devis['token'],
                    "date_traitement" => null,
                    "date_confirmation" => null,
                    "par" => null
                ]
            ];
        }

        foreach ($lesDocuments as $file) {
            $relativePath = str_replace("$basePath/", '', $file);
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
                $documents[$dirPath]['status'] = 'Valide';
            }

            if (str_ends_with($file, '.json')) {
                try {
                    $jsonContent = Storage::disk('public')->get($file);
                    $jsonData = json_decode($jsonContent, true);

                    $pathToken = "app/public/" . $file;
                    $token = Token::where('paths', $pathToken)->first();
                    
                    // Vérification de l'existence du token avant d'accéder à sa propriété
                    if ($token) {
                        $documents[$dirPath]["token_rapport"] = $token->token;
                    } else {
                        \Log::warning("Token non trouvé pour le fichier JSON: $file");
                    }

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

        $filteredDocs = array_values(array_filter($documents, fn($doc) => strpos($doc['path'], '/') !== false));
        return response()->json($filteredDocs);
        
    } catch (\Exception $e) {
        \Log::error('Erreur dans listSavedDocs: ' . $e->getMessage());
        return response()->json(['error' => 'Une erreur est survenue: ' . $e->getMessage()], 500);
    }
}

    // Renvoie le fichiers JSON d'un documents
    public function open(Request $request, $token): JsonResponse
    {

        $dataToken = Token::where('token', $token)->get()->first();
        $data = null;

        try {
            // Appel de la fonction qui récupère les données du token
            $data = self::getJSONDataFromToken($dataToken);

            $document = $data['dataToken']['document'];
            $client = $data['dataToken']['client'];
            $uid = $data['dataToken']['uid'];
        } catch (\Throwable $th) {
            abort('500', "erreur interne");
        }

        // Chemin vers le fichier JSON (toutes les données du document)
        $jsonpath = "$client/$document/$uid/$uid.json";

        if (!Storage::disk('public')->exists($jsonpath)) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        $content = Storage::disk('public')->get($jsonpath);
        $jsonResponse = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Fichier JSON invalide'], 500);
        }

        return response()->json($jsonResponse ?: new \stdClass());
    }


    // Permet de supprimer un document
    public function delete(Request $request, $token): JsonResponse
    {
        $dataToken = Token::where('token', $token)->get()->first();

        $data = null;

        try {
            // Appel de la fonction qui récupère les données du token
            $data = self::getJSONDataFromToken($dataToken);

            $document = $data['dataToken']['document'];
            $client = $data['dataToken']['client'];
            $uid = $data['dataToken']['uid'];
        } catch (\Throwable $th) {
            abort('500', "erreur interne");
        }


        $path = "$client/$document/$uid";

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'Folder not found.'], 404);
        }

        if (!Storage::disk('public')->deleteDirectory($path)) {
            return response()->json(['error' => 'Failed to delete folder.'], 500);
        }

        $dataToken->delete();

        return response()->json(['status' => 'Success.'], 200);
    }
}
