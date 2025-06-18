<?php

use App\Http\Controllers\DevisController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SignatureController;

use Illuminate\Support\Facades\Route;


// Route pour les devis
Route::prefix('devis')->group(function () {

    Route::middleware(['throttle:anti-bruteforce'])->group(function () {

        //Supprime un devis
        Route::get('/delete-devis/{noToken}', [DevisController::class,'delete']);

        // Affichage d'un PDF de devis
        Route::get('/pdf-devis/{token}',[PdfController::class,'viewDevis']);

    });


    Route::get('/check-devis/{noToken}',[DevisController::class,'check']);

    //Routes pour la signature des devis
    Route::get('/signature/{token}', [SignatureController::class, 'show'])->name('signature.show')->middleware('VerifToken');
    Route::post('/signature/{token}', [SignatureController::class, 'sign'])->name('signature.sign')->middleware('VerifToken');

    // Telechargement devis pdf
    Route::get('/devis/{token}', [SignatureController::class, 'devis'])->middleware('VerifToken');

    Route::get('/download-devis/{client}/{filename}', function ($client, $uid) {
        $filePath = storage_path('app/public/'.$client.'/devis/'.$uid. '/' . $uid .'_certifie.pdf');
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, "{$uid}.pdf");
    });
    
});