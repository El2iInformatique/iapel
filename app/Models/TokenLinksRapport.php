<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @class TokenLinksRapport
 * @brief Modèle de gestion des tokens d'accès aux rapports et documents d'intervention.
 *
 * Ce modèle centralise la gestion des tokens sécurisés permettant l'accès aux rapports
 * d'intervention, formulaires CERFA et autres documents générés par l'application.
 * Il établit la liaison entre un token unique et le chemin physique du document associé.
 * 
 * Fonctionnalités principales :
 * - Génération de tokens d'accès sécurisés avec durée de validité.
 * - Liaison token/chemin de fichier pour l'accès aux documents.
 * - Gestion de l'expiration automatique des accès.
 * - Support des rapports d'intervention, CERFA 15497, CERFA 13948-03.
 * - Traçabilité des accès aux documents sensibles.
 *
 * @package App\Models
 * @version 2.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Les tokens expirent automatiquement après un mois pour des raisons de sécurité.
 * @note La table utilisée est "token_links_rapport" et n'utilise pas les timestamps Laravel.
 * @warning Les chemins stockés doivent être relatifs au répertoire storage/app/ de Laravel.
 * @see BiController Pour l'utilisation des tokens dans le contexte des rapports.
 * @see TokenController Pour la génération des tokens de signature de devis.
 */
class TokenLinksRapport extends Model
{
    use HasFactory;

    /**
     * @brief Les attributs qui peuvent être assignés en masse.
     * 
     * Définit les champs de la base de données qui peuvent être remplis
     * lors de la création ou mise à jour d'un enregistrement.
     * 
     * @var array<string> Liste des attributs mass-assignables.
     */
    protected $fillable = ['token', 'documents', 'paths', 'expires_at', 'created_at'];
    
    /**
     * @brief Nom de la table de base de données.
     * 
     * @var string Nom de la table utilisée pour ce modèle.
     */
    protected $table = "token_links_rapport";
    
    /**
     * @brief Désactive la gestion automatique des timestamps Laravel.
     * 
     * @var bool False pour désactiver les colonnes created_at/updated_at automatiques.
     */
    public $timestamps = false;

    /**
     * @brief Génère un nouveau token d'accès pour un rapport/document.
     *
     * Cette méthode statique crée un nouveau token d'accès sécurisé lié à un document
     * spécifique. Le token permet l'accès au document via une URL sécurisée pendant
     * une durée limitée d'un mois.
     *
     * Le token généré permet :
     * - L'affichage du formulaire/rapport via une URL publique.
     * - La soumission des données du formulaire.
     * - Le téléchargement du PDF généré.
     * - L'accès sécurisé sans authentification utilisateur.
     *
     * @param string $token Token unique généré (généralement par Str::random()).
     * @param string $path Chemin relatif vers le fichier JSON du document (depuis storage/app/).
     * 
     * @return TokenLinksRapport Instance du modèle créé avec les données du token.
     * 
     * @throws \Illuminate\Database\QueryException Si la création en base échoue.
     * 
     * @example
     * ```php
     * // Génération d'un token pour un rapport d'intervention
     * $token = Str::random(40);
     * $path = "app/public/client1/rapport_intervention/uid123/uid123.json";
     * 
     * $tokenLink = TokenLinksRapport::generateTokenRapport($token, $path);
     * 
     * // URL d'accès générée : /bi/{token}
     * $url = url('/bi/' . $token);
     * ```
     * 
     * @example
     * ```php
     * // Génération d'un token pour un formulaire CERFA
     * $token = Str::random(40);
     * $path = "app/public/entreprise1/cerfa_15497/uid456/uid456.json";
     * 
     * $tokenLink = TokenLinksRapport::generateTokenRapport($token, $path);
     * ```
     * 
     * @note Le token expire automatiquement après un mois pour des raisons de sécurité.
     * @note La date de création est stockée au format Y-m-d.
     * @warning Le chemin doit pointer vers un fichier JSON valide et existant.
     * @see BiController::createJson() Méthode qui utilise cette fonction pour créer les tokens.
     */
    public static function generateTokenRapport( $token, $path ) {
        return self::create([
            'token' => $token,
            'paths' => $path,
            'expires_at' => now()->addMonth(),
            'created_at' => now()->format('Y-m-d'),
        ]);
    }
    
}
