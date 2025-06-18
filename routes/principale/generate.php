<?php

use App\Http\Controllers\PdfController;

use Illuminate\Support\Facades\Route;


// Route pour les devis
Route::prefix('generate')->group(function () {

    // routes de générations des pdfs
    Route::get('/generate-cerfa_15497', [PdfController::class, 'generateCerfa']);
    Route::get('/generate-cerfa_15497_1', [PdfController::class, 'generateCerfa']);
    Route::get('/generate-cerfa_15497_2', [PdfController::class, 'generateCerfa']);
    Route::get('/generate-cerfa_13948-03', [PdfController::class, 'generateAttestationTVA']);
    Route::get('/generate-rapport_intervention', [PdfController::class, 'generateBi']);

    Route::post('/generate-cerfa_13948-03', [PdfController::class, 'generateAttestationTVA']);

    Route::post('/generate-download-pdf', [PdfController::class, 'generateDownloadPDF']);


    
});