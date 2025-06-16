<?php

namespace App\Http\Controllers;

use App\Models\layou_client;
use App\Http\Controllers\TokenController;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


use App\Models\TokenLinksRapport;


class BiController extends Controller
{
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

    // fonction de création du JSON et du TOKEN d'identification du fichier
    public function createJson(Request $request)
    {

        $data = $request->input();

        $uid = $data['uid'];
        $document = $data['document'];
        $client = $data['client'];
        $folderPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid);

        // Vérifier si le dossier existe, sinon le créer
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

        $jsonPath = $folderPath . '/' . $uid . '.json';
        $dataToken = [
            'client' => $client,
            'document' => $document,
            'uid' => $uid
        ];

        try {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }

            if (str_starts_with($document, 'cerfa_15497')) {

              $jsonData = [
                  'dataToken' => $dataToken,
                    'operateur' => $data['Operateur'] ?? '',
                    'detenteur' => $data['Detenteur'] ?? ''
                ];

            } else if ($document == 'cerfa_13948-03') {

              $jsonData = [
                  'dataToken' => $dataToken,
                    'nom' => $data['nom'] ?? '',
                    'prenom' => $data['prenom'] ?? '',
                    'adresse' => $data['adresse'] ?? '',
                    'commune' => $data['commune'] ?? '',
                    'code_postal' => $data['code_postal'] ?? ''
                ];
            } else if ($document == 'rapport_intervention') {

                $jsonData = [
                    'dataToken' => $dataToken,
                    'code_client' => $data['code_client'] ?? '',
                    'nom_client' => $data['nom_client'] ?? '',
                    'email_client' => $data['email_client'] ?? '',
                    'telephone_client' => $data['telephone_client'] ?? '',
                    'portable_client' => $data['portable_client'] ?? '',
                    'adresse_facturation' => $data['adresse_facturation'] ?? '',
                    'cp_facturation' => $data['cp_facturation'] ?? '',
                    'ville_facturation' => $data['ville_facturation'] ?? '',
                    'lieu_intervention' => $data['lieu_intervention'] ?? '',
                    'adresse_intervention' => $data['adresse_intervention'] ?? '',
                    'cp_intervention' => $data['cp_intervention'] ?? '',
                    'ville_intervention' => $data['ville_intervention'] ?? '',
                    'intervenant' => $data['intervenant'] ?? '',
                    'description' => $data['description'] ?? ''
                ];
            }

            $token = TokenController::generateTokenRapport( $request, 'app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json' );

            if (!$token) {
                return response()->json([
                    'error' => 'Token non généré',
                ]);
            }
            file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));

            return response()->json([
                'message' => 'Token généré avec succès',
                'token' => $token,
                'bi_url' => url('/bi/' . $token),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du fichier JSON : ' . $e->getMessage());
        }
    }


    public function download($token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            abort(404, 'Fichier JSON introuvable');
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        $filePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        return response()->download($filePath, "{$uid}.pdf");
    }

    public function check($client, $document, $uid, Request $request)
    {
        if (!$request->hasHeader('secret-token')) {
            return response()->json(['error' => 'No token provided.'], 403);
        }

        $secretToken = config("secrets.$client");
        $adminToken = config('secrets.admin');

        $providedToken = $request->header('secret-token');

        if (!hash_equals($providedToken,     $secretToken) && !hash_equals($providedToken, $adminToken)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        $jsonPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json');
        $pdfPath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.pdf');

        return response()->json([
            'json_exists' => file_exists($jsonPath),
            'pdf_exists' => file_exists($pdfPath)
        ]);
    }

    public function show($token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

        // Construire le chemin du fichier JSON
        $filePath = storage_path( $dataToken['paths']);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            abort(404, 'Fichier JSON introuvable');
        }

        // Lire et décoder le fichier JSON
        $data = json_decode(file_get_contents($filePath), true);

        $client = $data["dataToken"]["client"];
        $document = $data["dataToken"]["document"];
        $uid = $data["dataToken"]["uid"];
        $token = $dataToken->token;

        if (str_starts_with($document, 'cerfa_15497_')) {
            return view('cerfa_15497', compact('data', 'token', 'uid', 'client', 'document'));
        }
        else if (str_starts_with($document, 'cerfa')) {
            return view($document, compact('data', 'token', 'uid', 'client', 'document'));
        } else {

            $client_layout = layou_client::where('nom_client', $client)->first();

            if (!$client_layout) {
                return view('bi', compact('data', 'token', 'uid', 'client', 'document'));
            }
            else {
                return view('bi', compact('data', 'token', 'uid', 'client', 'document', 'client_layout'));
            }

        }
    }

    public function submit(Request $request, $token)
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

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
            if (!$request->hasHeader('secret-token')) {
                return response()->json(['error' => 'No token provided.'], 403);
            }



            $secretToken = config("secrets.$entreprise");
            $adminToken = config('secrets.admin');

            $providedToken = $request->header('secret-token');

            if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
                return response()->json(['error' => 'Not authorized.'], 403);
            }

            $basePath = "$entreprise";

            // if (!Storage::disk('public')->exists($basePath)) {
            //     return response()->json(['message' => "Le dossier '$basePath' n'existe pas."], 404);
            // }

            $files = Storage::disk('public')->allFiles($basePath);

            $lesDevis = array_filter($files, function ($file) {
                return explode('/', $file)[1] === 'devis';
            });

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

            $documents = [];
            $devisTraites = [];
            foreach ($lesDevis as $file) {
                $fileName = explode('/',$file)[3];

                $exploded = explode('_', $fileName);
                $devisName = explode('.', $exploded[0] . '_' . $exploded[1])[0];

                $token = Token::where('token', explode('.', $exploded[1])[0])->first();

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
                    $documents[$dirPath]['status'] = 'Validé';
                }

                if (str_ends_with($file, '.json')) {
                    try {
                        $jsonContent = Storage::disk('public')->get($file);
                        $jsonData = json_decode($jsonContent, true);

                        $pathToken = "app/public/" . $file;
                        $token = TokenLinksRapport::where('paths', $pathToken)->first()["token"];
                        $documents[$dirPath]["token_rapport"] = $token;

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
            //\Log::info(''. json_encode($documents));
            $filteredDocs = array_values(array_filter($documents, fn($doc) => strpos($doc['path'], '/') !== false));
            //\Log::info(''. json_encode($filteredDocs));
            return response()->json($filteredDocs);
        } catch (\Exception $e) {
            \Log::error('Erreur dans listSavedDocs: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue: ' . $e->getMessage()], 500);
        }
    }



    public function open($client, $document, $uid, Request $request): JsonResponse
    {

        if (!$request->hasHeader('secret-token')) {
            return response()->json(['error' => 'No token provided.'], 403);
        }

        $secretToken = config("secrets.$client");
        $adminToken = config('secrets.admin');

        $providedToken = $request->header('secret-token');

        if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }


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



    public function delete(Request $request, $token): JsonResponse
    {
        $dataToken = TokenLinksRapport::where('token', $token)->get()->first();

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
