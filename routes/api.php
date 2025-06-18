<?php
use App\Http\Controllers\DevisController;
use App\Models\Token;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;



Route::prefix('api')->group(function () {
    
    Route::middleware(['throttle:anti-bruteforce'])->group(function () {
        
        // Création du JSON de données pour le document devis
        Route::post('/generate-token-devis', [TokenController::class, 'generateDevis'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->middleware('VerifHeaderPassword');
       
        // Création du JSON de données pour les documents rapport, cerfa
        Route::post('/generate-token-rapport-cerfa', [TokenController::class, 'generateOther'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->middleware('VerifHeaderPassword');;

        // Route de validation du token
        Route::get('/validate-token/{token}', [TokenController::class, 'validateToken']);

    });


    Route::get('/tokens', function () {
        return response()->json(Token::all());
    });
    Route::post('/upload-pdf', [PdfController::class, 'upload'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

});