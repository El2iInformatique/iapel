<?php
use App\Http\Controllers\DevisController;
use App\Models\Token;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('/generate-token', [TokenController::class, 'generate'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('/validate-token/{token}', [TokenController::class, 'validateToken']);
    Route::get('/delete-devis/{noToken}', [DevisController::class,'delete']);
    Route::get('/check-devis/{noToken}',[DevisController::class,'check']);
    Route::get('/tokens', function () {
        return response()->json(Token::all());
    });
    Route::post('/upload-pdf', [PdfController::class, 'upload'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});