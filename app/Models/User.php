<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @class User
 * @brief Modèle d'authentification et de gestion des utilisateurs de l'application.
 *
 * Ce modèle hérite de la classe Authenticatable de Laravel et gère tous les aspects
 * liés aux utilisateurs de l'application IAPEL. Il fournit les fonctionnalités
 * d'authentification, de gestion des sessions et de notification des utilisateurs.
 * 
 * Fonctionnalités principales :
 * - Authentification des utilisateurs (login/logout).
 * - Gestion des mots de passe sécurisés avec hachage automatique.
 * - Support des notifications par email.
 * - Gestion des sessions utilisateur.
 * - Support des factories pour les tests et le seeding.
 * - Protection contre l'exposition des données sensibles.
 *
 * @package App\Models
 * @version 1.0
 * @author Maxime ENTZ LPB
 * @since 1.0
 * @note Utilise la table "users" par défaut de Laravel.
 * @note Les mots de passe sont automatiquement hachés lors de l'assignation.
 * @note Le token de remember_me est caché lors de la sérialisation.
 * @warning Ne jamais exposer les mots de passe ou tokens dans les réponses API.
 * @see \Database\Factories\UserFactory Pour la génération de données de test.
 * @example
 * ```php
 * // Création d'un nouvel utilisateur
 * $user = User::create([
 *     'name' => 'Jean Dupont',
 *     'email' => 'jean.dupont@example.com',
 *     'password' => 'motdepasse123' // Sera automatiquement haché
 * ]);
 * 
 * // Authentification
 * if (Auth::attempt(['email' => $email, 'password' => $password])) {
 *     // Utilisateur connecté
 * }
 * ```
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @brief Les attributs qui peuvent être assignés en masse.
     *
     * Définit les champs qui peuvent être remplis lors de la création
     * ou mise à jour d'un utilisateur via l'assignation de masse.
     * Cette protection évite les vulnérabilités de type "mass assignment".
     *
     * @var list<string> Liste des attributs mass-assignables.
     * 
     * @note Le mot de passe sera automatiquement haché grâce au cast 'hashed'.
     * @warning Ne jamais ajouter des champs sensibles comme 'is_admin' sans validation.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @brief Les attributs qui doivent être cachés lors de la sérialisation.
     *
     * Ces attributs ne seront pas inclus dans les réponses JSON ou
     * lors de la conversion du modèle en tableau, protégeant ainsi
     * les informations sensibles.
     *
     * @var list<string> Liste des attributs à cacher.
     * 
     * @note Le password est caché pour éviter son exposition accidentelle.
     * @note Le remember_token est utilisé pour la fonctionnalité "Se souvenir de moi".
     * @warning Ces attributs restent accessibles via les accesseurs directs ($user->password).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @brief Obtient les attributs qui doivent être castés.
     *
     * Définit comment certains attributs doivent être automatiquement
     * convertis lors de la récupération ou de l'assignation.
     *
     * @return array<string, string> Tableau associatif des casts à appliquer.
     * 
     * @note 'email_verified_at' est casté en datetime pour faciliter la manipulation des dates.
     * @note 'password' est casté en 'hashed' pour le hachage automatique lors de l'assignation.
     * @example
     * ```php
     * $user = new User();
     * $user->password = 'motdepasse123'; // Sera automatiquement haché
     * $user->email_verified_at = now(); // Sera converti en Carbon\Carbon
     * ```
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @brief Vérifie si l'utilisateur a vérifié son email.
     *
     * Méthode utilitaire pour vérifier rapidement si un utilisateur
     * a confirmé son adresse email.
     *
     * @return bool True si l'email est vérifié, false sinon.
     * 
     * @example
     * ```php
     * if ($user->hasVerifiedEmail()) {
     *     // L'utilisateur peut accéder aux fonctionnalités complètes
     * } else {
     *     // Rediriger vers la page de vérification d'email
     * }
     * ```
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * @brief Marque l'email de l'utilisateur comme vérifié.
     *
     * Met à jour le timestamp de vérification d'email pour indiquer
     * que l'utilisateur a confirmé son adresse email.
     *
     * @return bool True si la mise à jour a réussi, false sinon.
     * 
     * @example
     * ```php
     * // Lors de la validation d'un lien de vérification d'email
     * if ($user->markEmailAsVerified()) {
     *     return redirect()->route('dashboard')->with('success', 'Email vérifié avec succès');
     * }
     * ```
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * @brief Obtient l'adresse email pour la réinitialisation de mot de passe.
     *
     * Détermine quelle adresse email utiliser pour envoyer les liens
     * de réinitialisation de mot de passe.
     *
     * @return string Adresse email de l'utilisateur.
     * 
     * @note Par défaut, utilise l'attribut 'email' du modèle.
     * @note Peut être surchargée pour utiliser un email différent si nécessaire.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->email;
    }
}
