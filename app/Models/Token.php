<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @class Token
 * @brief Modèle de gestion des tokens de signature électronique pour les devis.
 *
 * Ce modèle centralise la gestion des tokens sécurisés permettant la signature électronique
 * des devis par les clients. Il stocke toutes les informations nécessaires à la génération
 * du document de signature et au positionnement des éléments graphiques (signatures, dates).
 * 
 * Fonctionnalités principales :
 * - Génération de tokens uniques avec durée de validité limitée.
 * - Stockage des informations financières du devis (HT, TVA, TTC).
 * - Gestion des coordonnées de positionnement pour signatures et dates.
 * - Liaison avec l'organisation et le client pour traçabilité.
 * - Support de l'expiration automatique des tokens.
 * - Suivi de l'utilisation des tokens (signature effectuée ou non).
 *
 * @package App\Models
 * @version 2.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Les tokens expirent automatiquement après un mois pour des raisons de sécurité.
 * @note Utilise la table "tokens" en base de données avec gestion des timestamps Laravel.
 * @warning Les coordonnées de signature doivent être validées côté client avant stockage.
 * @see TokenController Pour l'utilisation des tokens dans le processus de signature.
 * @see BiController::listSavedDocs() Méthode qui utilise ce modèle pour lister les devis.
 * @example
 * ```php
 * // Génération d'un token pour un devis
 * $coords = [
 *     'x_signature' => 100,
 *     'y_signature' => 200,
 *     'x_date' => 300,
 *     'y_date' => 400
 * ];
 * 
 * $token = Token::generateToken(
 *     1, 
 *     'Client SARL', 
 *     123, 
 *     'client@example.com',
 *     'Devis travaux électriques',
 *     1000.00,
 *     200.00,
 *     1200.00,
 *     $coords,
 *     3
 * );
 * 
 * // URL de signature générée
 * $signatureUrl = url('/signer/' . $token->token);
 * ```
 */
class Token extends Model {
    use HasFactory;

    /**
     * @brief Les attributs qui peuvent être assignés en masse.
     *
     * Définit les champs de la base de données qui peuvent être remplis
     * lors de la création ou mise à jour d'un token de signature.
     * Inclut toutes les données nécessaires au processus de signature.
     *
     * @var array<string> Liste des attributs mass-assignables.
     * 
     * @note Tous les montants sont stockés en format décimal pour précision comptable.
     * @note Les coordonnées de signature sont en pixels relatifs au document PDF.
     * @warning L'attribut 'used' permet de s'assurer qu'un token n'est utilisé qu'une seule fois.
     */
    protected $fillable = [
        'token', 
        'organisation_id', 
        'tiers',
        'client_email', 
        'devis_id', 
        'used', 
        'expires_at', 
        'titre', 
        'montant_HT', 
        'montant_TVA', 
        'montant_TTC',
        'x_signature',
        'y_signature',
        'x_date',
        'y_date',
        'nb_pages'
    ];

    /**
     * @brief Génère un nouveau token de signature pour un devis.
     *
     * Cette méthode statique crée un token sécurisé unique avec toutes les informations
     * nécessaires pour permettre la signature électronique d'un devis. Le token contient
     * les données financières, les coordonnées de positionnement des signatures et dates,
     * ainsi que les informations de traçabilité.
     *
     * Le token généré permet :
     * - L'accès sécurisé au document de signature via une URL unique.
     * - L'affichage des informations du devis au client.
     * - Le positionnement précis des signatures et dates sur le PDF.
     * - La traçabilité complète de la signature (qui, quand, quoi).
     * - L'expiration automatique après un mois.
     *
     * @param int $organisationId Identifiant de l'organisation émettrice du devis.
     * @param string $tiers Nom ou raison sociale du client/tiers signataire.
     * @param int $devisId Identifiant unique du devis dans le système.
     * @param string $clientEmail Adresse email du client pour notifications.
     * @param string $titre Titre ou description courte du devis.
     * @param float $montantHT Montant hors taxes en euros.
     * @param float $montantTVA Montant de la TVA en euros.
     * @param float $montantTTC Montant toutes taxes comprises en euros.
     * @param array $coords Coordonnées de positionnement des éléments de signature.
     * @param int $nbpages Nombre de pages du document PDF à signer.
     * 
     * @return Token Instance du token créé avec toutes les données.
     * 
     * @throws \Illuminate\Database\QueryException Si la création en base échoue.
     * 
     * @example
     * ```php
     * // Coordonnées pour un document A4 (595x842 points PDF)
     * $coords = [
     *     'x_signature' => 150,  // Position X de la signature client
     *     'y_signature' => 100,  // Position Y de la signature client  
     *     'x_date' => 400,       // Position X de la date de signature
     *     'y_date' => 100        // Position Y de la date de signature
     * ];
     * 
     * $token = Token::generateToken(
     *     1,                              // ID organisation
     *     'Entreprise Martin SARL',       // Nom du client
     *     456,                            // ID du devis
     *     'martin@entreprise.com',        // Email client
     *     'Installation électrique',     // Titre du devis
     *     2500.00,                        // Montant HT
     *     500.00,                         // Montant TVA (20%)
     *     3000.00,                        // Montant TTC
     *     $coords,                        // Coordonnées
     *     2                               // Nombre de pages
     * );
     * 
     * echo "Token généré : " . $token->token;
     * echo "Expire le : " . $token->expires_at;
     * ```
     * 
     * @example
     * ```php
     * // Génération pour un petit devis simple
     * $coords = [
     *     'x_signature' => 100,
     *     'y_signature' => 50,
     *     'x_date' => 300,
     *     'y_date' => 50
     * ];
     * 
     * $token = Token::generateToken(
     *     2, 'Particulier Dupont', 789, 
     *     'dupont@email.com', 'Réparation urgente',
     *     150.00, 30.00, 180.00, $coords, 1
     * );
     * ```
     * 
     * @note Le token généré fait 40 caractères aléatoires pour sécurité maximale.
     * @note L'expiration est fixée à un mois après la création du token.
     * @note Les coordonnées doivent être en points PDF (1 point = 1/72 pouce).
     * @warning Vérifier la cohérence des montants (HT + TVA = TTC) avant appel.
     * @warning S'assurer que les coordonnées sont dans les limites du document.
     * @see Str::random() Pour la génération du token aléatoire sécurisé.
     */
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

    /**
     * @brief Vérifie si le token est encore valide.
     *
     * Contrôle que le token n'a pas expiré et n'a pas encore été utilisé
     * pour une signature. Un token expiré ou déjà utilisé ne peut plus
     * servir à signer un document.
     *
     * @return bool True si le token est valide et utilisable, false sinon.
     * 
     * @example
     * ```php
     * $token = Token::where('token', $tokenString)->first();
     * 
     * if ($token && $token->isValid()) {
     *     // Permettre l'accès à la signature
     *     return view('signature', compact('token'));
     * } else {
     *     // Token expiré ou déjà utilisé
     *     return redirect()->route('error')->with('message', 'Token invalide');
     * }
     * ```
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at > now();
    }

    /**
     * @brief Marque le token comme utilisé après signature.
     *
     * Met à jour le statut du token pour indiquer qu'il a été utilisé
     * pour signer le document. Ceci empêche toute réutilisation du token.
     *
     * @return bool True si la mise à jour a réussi, false sinon.
     * 
     * @example
     * ```php
     * // Après validation de la signature
     * if ($signatureValidated) {
     *     $token->markAsUsed();
     *     
     *     // Envoyer notification de signature confirmée
     *     Mail::to($token->client_email)->send(new SignatureConfirmation($token));
     * }
     * ```
     */
    public function markAsUsed(): bool
    {
        $this->used = true;
        return $this->save();
    }

    /**
     * @brief Vérifie si le token va expirer bientôt.
     *
     * Détermine si le token expire dans les prochaines 24 heures,
     * permettant d'envoyer des rappels au client.
     *
     * @return bool True si le token expire dans moins de 24h, false sinon.
     * 
     * @example
     * ```php
     * // Système de rappels automatiques
     * $tokensExpiringsoon = Token::where('used', false)
     *                           ->where('expires_at', '>', now())
     *                           ->get()
     *                           ->filter(fn($token) => $token->isExpiringSoon());
     * 
     * foreach ($tokensExpiringsSoon as $token) {
     *     Mail::to($token->client_email)->send(new ExpirationReminder($token));
     * }
     * ```
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at <= now()->addDay() && $this->expires_at > now();
    }

    /**
     * @brief Calcule le montant total avec vérification de cohérence.
     *
     * Vérifie que la somme HT + TVA correspond bien au montant TTC
     * et retourne le montant TTC avec une marge d'erreur acceptable.
     *
     * @return float Montant TTC vérifié.
     * @throws \InvalidArgumentException Si les montants sont incohérents.
     * 
     * @example
     * ```php
     * try {
     *     $montantVerifie = $token->getVerifiedTotal();
     *     echo "Montant TTC vérifié : " . number_format($montantVerifie, 2) . " €";
     * } catch (\InvalidArgumentException $e) {
     *     Log::error("Incohérence montants token {$token->id}: " . $e->getMessage());
     * }
     * ```
     */
    public function getVerifiedTotal(): float
    {
        $calculatedTTC = $this->montant_HT + $this->montant_TVA;
        $difference = abs($calculatedTTC - $this->montant_TTC);
        
        // Tolérance d'erreur de 0.01€ pour les arrondis
        if ($difference > 0.01) {
            throw new \InvalidArgumentException(
                "Incohérence montants: HT({$this->montant_HT}) + TVA({$this->montant_TVA}) ≠ TTC({$this->montant_TTC})"
            );
        }
        
        return $this->montant_TTC;
    }
}
