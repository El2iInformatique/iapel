<?php
use App\Http\Controllers\DevisController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\BiController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use App\Services\ClientConfigurationService;
use App\Http\Controllers\ClientConfigurationController;

Route::prefix('api')->group(function () {
    
    /*
        DEVIS
    */
    Route::post('/generate-token', [DevisController::class, 'createJson'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifSecretToken');
    Route::get('/validate-token/{token}', [TokenController::class, 'validateToken']);
    Route::get('/delete-devis/{noToken}', [DevisController::class,'delete']);
    Route::post('/upload-pdf', [PdfController::class, 'upload'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    Route::post('/devis-refuse/{token}', [DevisController::class, 'refuse'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

        
    /*
        RAPPORT INTERVENTION / CERFA
    */
    Route::post('/create-json', [BiController::class, 'createJson'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifSecretToken');
    //Suppression d'un document
    Route::delete('/delete/{token}', [BiController::class, 'delete'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifTokenAndSecretToken');

    // Téléchargement du document d'intervention réalisé
    Route::get('/download/{token}', [BiController::class, 'download'])->middleware('VerifTokenAndSecretToken');

    // Routes de création Client/Documents
    Route::get('/modeleExist/{client}/{document}', [ClientController::class, 'modeleExists']);

    Route::post('/createDocument', [ClientController::class, 'createDocument'])
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->middleware('VerifSecretToken');

    Route::get('/getDocument/{client}', [ClientController::class, 'getDocument']);


    //Routes temporaire à améliorer si besoin
    Route::prefix('clients/{client}')->group(function () {
        Route::get('/configuration/bi/options', [ClientConfigurationController::class, 'getBiOptions'])->middleware('VerifTokenAndSecretToken');
        Route::put('/configuration/bi/options', [ClientConfigurationController::class, 'updateBiOptions'])->middleware('VerifTokenAndSecretToken');

        Route::get('/configuration/bi/cases-supplementaires', [ClientConfigurationController::class, 'getBiCasesSupplementaires'])->middleware('VerifTokenAndSecretToken');
        Route::put('/configuration/bi/cases-supplementaires', [ClientConfigurationController::class, 'updateBiCasesSupplementaires'])->middleware('VerifTokenAndSecretToken');

        Route::get('/configuration/bi/cerfa', [ClientConfigurationController::class, 'getBiCerfa'])->middleware('VerifTokenAndSecretToken');
        Route::put('/configuration/bi/cerfa', [ClientConfigurationController::class, 'updateBiCerfa'])->middleware('VerifTokenAndSecretToken');
    });


});