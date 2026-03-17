<?php

use App\Http\Controllers\TokenController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

use App\Http\Controllers\PdfController;
use App\Http\Controllers\CerfaController;
use App\Http\Controllers\BiController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\RevisionController;
use Illuminate\Http\Request;



require base_path('routes/api.php');


// Routes pour APEL Bâtiment mobile + Desktop pour la gestion des documents en ligne

// Route avec protection
// Définition de la limite de taux pour le type de requête "rapport"


RateLimiter::for('anti-bruteforce-rapport', function (Request $request) {
    return Limit::perMinute(20)->by($request->ip()); // 20 requêtes par minute par IP
});

Route::middleware(['throttle:anti-bruteforce-rapport'])->group(function () {

    // Formulaire de saisie du document d'intervention
    Route::get('/bi/{token}', [BiController::class, 'show'])->name('bi.view')->middleware('VerifToken');

    // Envoi des formulaires de saisies des documents d'intervention
    Route::post('/submit/{token}', [BiController::class, 'submit'])->name('bi.submit')->middleware('VerifToken');;

    // Génération et affichage des PDFs
    Route::get('/pdf/{token}', [PdfController::class, 'show'])->name('pdf.view')->middleware('VerifToken');

    // Unigned PDF view
    

    // Data d'un document
    Route::get('/open/{token}', [BiController::class, 'open'])->middleware('VerifTokenAndSecretToken');

    //Suppression d'un document
    Route::delete('/delete/{token}', [BiController::class, 'delete'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifTokenAndSecretToken'); //Legacy

    // Téléchargement du document d'intervention réalisé
    Route::get('/download/{token}', [BiController::class, 'download'])->middleware('VerifToken'); // Legacy
    
});


Route::get('/unsigned', function() {
    return view('unsigned');
});

Route::get('/', function () {
    return redirect()->away('https://www.el2i.fr');
});

Route::get('/revision', [RevisionController::class, 'index'])->name('revision.index');
Route::post('/revision/check', [RevisionController::class, 'check'])->name('revision.check');


// Listing des documents disponibles pour un client
Route::get('/documents/{client}', [BiController::class, 'getDocuments']);

// Fonction de vérification de l'état du document d'intervention
Route::get('/check/{client}/{document}/{uid}', [BiController::class, 'check'])->middleware('VerifSecretToken');
// Listing de tous les documents enregistrés pour un client
Route::get('/list/{client}', [BiController::class, 'listSavedDocs'])->middleware('VerifSecretToken');
// Affichage d'un PDF de devis
Route::get('/pdf-devis/{token}',[PdfController::class,'viewDevis']);

Route::get('/getToken/{client}/{document}/{uid}',[TokenController::class,'getToken'])->middleware('VerifSecretToken');


Route::post('/upload-visuel', function(Request $request) {
    // 1. Validation de sécurité
    if (!$request->hasFile('image')) {
        return response()->json(['success' => false, 'message' => 'Aucune image'], 400);
    }

    //Illuminate\Support\Facades\Log

    $file = $request->file('image');
    $client = $request->input('client');
    $document = $request->input('document');
    $uid = $request->input('uid');

    // 2. Construction du chemin
    $name = 'compressed_' . time() . '_' . $file->getClientOriginalName() . ".jpg";

    $folder = "{$client}/{$document}/{$uid}/"; // On ajoute 'uploads' pour mieux organiser
    //Illuminate\Support\Facades\Log

    // 3. Stockage sur le disque 'public' (storage/app/public)
    $path = $file->storeAs($folder, $name, 'public');

    if ($path) {
        return response()->json([
            'success' => true,
            'name' => $name,
            // Génère l'URL correcte via le lien symbolique
            'url' => asset('storage/' . $path) 
        ]);
    }

    return response()->json(['success' => false, 'message' => 'Erreur lors du stockage'], 500);
});

Route::post('/delete-visuel', function(Request $request) {
    $name = $request->input('name');
    $client = $request->input('client');
    $document = $request->input('document');
    $uid = $request->input('uid');

    Illuminate\Support\Facades\Log::info("Folder : {$client}/{$document}/{$uid}/ | Name : {$name}");

    if ($name) {
        Storage::disk('public')->delete($client.'/'.$document.'/'.$uid.'/' . $name);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 400);
});


// routes de générations des pdfs
Route::get('/generate-cerfa_15497', [PdfController::class, 'generateCerfa']);
Route::get('/generate-cerfa_15497_1', [PdfController::class, 'generateCerfa']);
Route::get('/generate-cerfa_15497_2', [PdfController::class, 'generateCerfa']);
Route::get('/generate-rapport_intervention', [PdfController::class, 'generateBi']);


Route::post('/generate-download-pdf', [PdfController::class, 'generateDownloadPDF']);


//Routes pour la signature des devis
Route::get('/signature/{token}', [SignatureController::class, 'show'])->name('signature.show');
Route::post('/signature/{token}', [SignatureController::class, 'sign'])->name('signature.sign');
Route::post('/signature-fullname/{token}', [SignatureController::class, 'signWithFullName'])->name('signature.signFullName');

Route::get('/devis/{client}/{uid}', function ($client, $uid) {
    $filePath = storage_path('app/public/'.$client.'/devis/'.$uid. '/' . $uid . '.pdf');
    if (!file_exists($filePath)) {
        abort(404);
    }

    return Response::file($filePath, [
        'Content-Type' => 'application/pdf',
    ]);
});


Route::get('/download-devis/{client}/{filename}', function ($client, $uid) {
    $filePath = storage_path('app/public/'.$client.'/devis/'.$uid. '/' . $uid .'_certifie.pdf');
    if (!file_exists($filePath)) {
        abort(404);
    }

    return response()->download($filePath, "{$uid}.pdf");
});
