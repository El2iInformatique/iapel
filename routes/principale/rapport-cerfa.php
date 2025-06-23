<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\BiController;

use Illuminate\Support\Facades\Route;


// Route pour les devis
Route::prefix('rapport-cerfa')->group(function () {

    Route::middleware(['throttle:anti-bruteforce'])->group(function () {

        // Formulaire de saisie du document d'intervention
        Route::get('/bi/{token}', [BiController::class, 'show'])->name('bi.view')->middleware('VerifToken');
    
        // Envoi des formulaires de saisies des documents d'intervention
        Route::post('/submit/{token}', [BiController::class, 'submit'])->name('bi.submit')->middleware('VerifToken');;
    
        // Génération et affichage des PDFs
        Route::get('/pdf/{token}', [PdfController::class, 'show'])->name('pdf.view')->middleware('VerifToken');
    
        // Data d'un document
        Route::get('/open/{token}', [BiController::class, 'open'])->middleware('HeaderVerifToken');

        
    });
    

    // Listing des documents disponibles pour un client
    Route::get('/documents/{client}', [BiController::class, 'getDocuments']);

    // Téléchargement du document d'intervention réalisé
    Route::get('/download/{token}', [BiController::class, 'download'])->middleware('VerifToken');

    // Fonction de vérification de l'état du document d'intervention
    Route::get('/check/{token}', [BiController::class, 'check'])->middleware('HeaderVerifToken');

    // Listing de tous les documents enregistrés pour un client
    Route::get('/list/{client}', [BiController::class, 'listSavedDocs'])->middleware('VerifHeaderPassword');
    
});