<?php
use App\Http\Controllers\DevisController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\BiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('/generate-token', [TokenController::class, 'generate'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifSecretToken');
    Route::get('/validate-token/{token}', [TokenController::class, 'validateToken']);
    Route::get('/delete-devis/{noToken}', [DevisController::class,'delete']);
    Route::get('/check-devis/{noToken}',[DevisController::class,'check']);
    
    Route::post('/upload-pdf', [PdfController::class, 'upload'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/create-json', [BiController::class, 'createJson'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifSecretToken');

    //Suppression d'un document
    Route::get('/delete/{token}', [BiController::class, 'delete'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->middleware('VerifTokenAndSecretToken');

    // Téléchargement du document d'intervention réalisé
    Route::get('/download/{token}', [BiController::class, 'download'])->middleware('VerifTokenAndSecretToken');
});