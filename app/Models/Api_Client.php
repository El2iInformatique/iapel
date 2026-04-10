<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api_Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_hash',
        'entreprise',
        'is_active',
        'created_at',
        // 'last_used_at' // Ajoute-le ici si tu comptes le mettre à jour plus tard
    ];
    protected $table = "api_client";
    public $timestamps = false;

    // Permet de vider le cache des que laravel met a jour la cles api / table api_client
    protected static function booted()
    {
        static::updated(function ($apiClient) {
            // On supprime le cache lié à ce hash précis
            \Illuminate\Support\Facades\Cache::forget('api_key_' . $apiClient->token_hash);
        });
    }

    protected static function generateKey($entreprise) {
        // On génère une clé API aléatoire de 64 caractères
        $plainToken = bin2hex(random_bytes(32)); // 32 bytes = 64 caractères hexadécimaux

        // On hache la clé pour le stockage sécurisé
        $hashedToken = hash('sha256', $plainToken);
        \Log::info("Génération d'une nouvelle clé API pour l'entreprise '{$entreprise}' avec le hash : {$hashedToken}");

        // Création du client API avec le token haché
        $client = self::create([
            'token_hash' => $hashedToken,
            'entreprise' => $entreprise,
            'is_active' => true, // Par défaut, on active le client
            'created_at' => now() // Enregistre la date de création
        ]);

        // Retourne la clé en clair pour que l'utilisateur puisse la copier (affichage ou autre)
        return $plainToken;
    }

    protected static function updateKey($entreprise) {

        self::where('entreprise', $entreprise)->delete(); // Supprime l'ancien client API pour cette entreprise
        // On génère une clé API aléatoire de 64 caractères
        $plainToken = self::generateKey($entreprise); // On peut réutiliser la méthode de génération

        // Retourne la clé en clair pour que l'utilisateur puisse la copier (affichage ou autre)
        return $plainToken;
    }

}
