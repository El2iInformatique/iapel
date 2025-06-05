<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\CerfaController;
use App\Http\Controllers\BiController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\SignatureController;
use Illuminate\Http\Request;


require base_path('routes/api.php');

Route::get('/', function () {
    return redirect()->away('https://www.el2i.fr');
});

// Routes pour APEL Bâtiment mobile + Desktop pour la gestion des documents en ligne

// Listing des documents disponibles pour un client
Route::get('/documents/{client}', [BiController::class, 'getDocuments']);
// Formulaire de saisie du document d'intervention
Route::get('/bi/{client}/{document}/{uid}', [BiController::class, 'show'])->name('bi.view');
// Création du JSON de données pour le document
Route::post('/create-json', [BiController::class, 'createJson']);
// Téléchargement du document d'intervention réalisé
Route::get('/download/{client}/{document}/{uid}', [BiController::class, 'download']);
// Fonction de vérification de l'état du document d'intervention
Route::get('/check/{client}/{document}/{uid}', [BiController::class, 'check']);
// Envoi des formulaires de saisies des documents d'intervention
Route::post('/submit/{client}/{document}/{uid}', [BiController::class, 'submit'])->name('bi.submit');
// Génération et affichage des PDFs
Route::get('/pdf/{client}/{document}/{uid}', [PdfController::class, 'show'])->name('pdf.view');
// Listing de tous les documents enregistrés pour un client
Route::get('/list/{client}', [BiController::class, 'listSavedDocs']);
// Data d'un document
Route::get('/open/{client}/{document}/{uid}', [BiController::class, 'open']);
// Suppression d'un document
Route::get('/delete/{client}/{document}/{uid}', [BiController::class, 'delete']);
// Affichage d'un PDF de devis
Route::get('/pdf-devis/{token}',[PdfController::class,'viewDevis']);


Route::post('/upload-visuel', function(Request $request) {
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $client = $request->input('client');
        $document = $request->input('document');
        $uid = $request->input('uid');
        $name = 'compressed_' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($client.'/'.$document.'/'.$uid, $name, 'public');

        return response()->json([
            'success' => true,
            'name' => $name, // On retourne uniquement le nom
            'url' => asset('storage/'.$client.'/'.$document.'/'.$uid.'/'. $name) // URL publique de l'image
        ]);
    }
    return response()->json(['success' => false], 400);
});
Route::post('/delete-visuel', function(Request $request) {
    $name = $request->input('name');
    $client = $request->input('client');
    $document = $request->input('document');
    $uid = $request->input('uid');
    if ($name) {
        Storage::disk('public')->delete($client.'/'.$document.'/'.$uid.'/' . $name);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 400);
});


// routes de générations des pdfs 
Route::get('/generate-cerfa_15497', [PdfController::class, 'generateCerfa']);
Route::get('/generate-cerfa_13948-03', [PdfController::class, 'generateAttestationTVA']);
Route::get('/generate-rapport_intervention', [PdfController::class, 'generateBi']);

Route::post('/generate-cerfa_13948-03', [PdfController::class, 'generateAttestationTVA']);

Route::post('/generate-download-pdf', [PdfController::class, 'generateDownloadPDF']);


//Routes pour la signature des devis
Route::get('/signature/{token}', [SignatureController::class, 'show'])->name('signature.show');
Route::post('/signature/{token}', [SignatureController::class, 'sign'])->name('signature.sign');

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



