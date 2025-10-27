<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Token extends Model {
    use HasFactory;

    protected $fillable = ['token', 'organisation_id', 'tiers','client_email', 'devis_id', 'used', 'expires_at', 'titre', 'montant_HT', 'montant_TVA', 
                            'montant_TTC','x_signature','y_signature','x_date','y_date','nb_pages'];

    public static function generateToken($organisationId, $tiers, $devisId, $clientEmail, $titre, $montantHT, $montantTVA, $montantTTC, $coords, $nbpages) {

        return self::create([
            'token' => Str::random(40),
            'organisation_id' => $organisationId,
            'tiers' => $tiers,
            'client_email' => $clientEmail,
            'devis_id' => $devisId,
            'expires_at' => now()->addMonth(),
            'titre' => $titre,
            'montant_HT' => $montantHT,
            'montant_TVA' => $montantTVA,
            'montant_TTC' => $montantTTC,
            'x_signature' => $coords['x_signature'],
            'y_signature' => $coords['y_signature'],
            'x_date' => $coords['x_date'],
            'y_date' => $coords['y_date'],
            'nb_pages' => $nbpages,
        ]);
    }

}
