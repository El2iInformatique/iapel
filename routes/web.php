<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;


use Illuminate\Http\Request;


// Import des routes
// Route pour les tokens
require base_path('routes/api.php');

// route pour les devis
require base_path('routes/principale/devis.php');

// Route pour les rapport et cerfa
require base_path('routes/principale/rapport-cerfa.php');

// Route pour la generation des pdf
require base_path('routes/principale/generate.php');



// Définition des limites de requete pour certaines routes
RateLimiter::for('anti-bruteforce', function (Request $request) {
    return Limit::perMinute(20)->by($request->ip()); // 15 requêtes par minute par IP
});


//Route sans protection pour test
Route::get('/test', function() {
    return view('fake-create-json');
});

Route::get('/68', function () {
    return redirect()->away('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
});

// Redirection en cas de demande de la route racine
Route::get('/', function () {
    return redirect()->away('https://www.el2i.fr');
});


// Récupere les images est les stock dans le bon dossier
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

// Supprime l'image demander
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


