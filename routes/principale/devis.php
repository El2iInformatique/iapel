<?php

use App\Http\Controllers\DevisController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SignatureController;

use Illuminate\Support\Facades\Route;


// Route pour les devis
Route::prefix('devis')->group(function () {

    Route::middleware(['throttle:anti-bruteforce'])->group(function () {

        //Supprime un devis
        Route::get('/delete-devis/{token}', [DevisController::class,'delete'])->middleware('HeaderVerifToken');

        // Affichage d'un PDF de devis
        Route::get('/pdf-devis/{token}',[PdfController::class,'viewDevis'])->middleware('VerifToken');
    });

    // Verifie si le devis existe / certifie ou non
    Route::get('/check-devis/{token}',[DevisController::class,'check'])->middleware('HeaderVerifToken');


    //Routes pour la signature des devis
    Route::get('/signature/{token}', [SignatureController::class, 'show'])->name('signature.show')->middleware('VerifToken');
    Route::post('/signature/{token}', [SignatureController::class, 'sign'])->name('signature.sign')->middleware('VerifToken');

    // Telechargement devis pdf || Permet dans signature.blade.php de voir le PDF dans un modèle requête POST depuis signature
    Route::get('/download-devis/{token}', [DevisController::class, 'download_devis'])->middleware('VerifToken');

    // Téléchargement du devis certifier + signature
    Route::get('/download-devis-certifie/{token}',[DevisController::class, 'download_devis_certifie'])->middleware('VerifToken');
    
    // /Permet d'uploader un PDF. D'abord création du token, puis depuis APEL récupération du token et renvoie sur cette route le PDF avec comme nom le token
    Route::post('/upload-pdf', [PdfController::class, 'upload'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
});