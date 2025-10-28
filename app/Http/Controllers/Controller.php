<?php

namespace App\Http\Controllers;

/**
 * @class Controller
 * @brief Classe de base abstraite pour tous les contrôleurs de l'application.
 *
 * Cette classe abstraite sert de fondation pour tous les contrôleurs HTTP de l'application Laravel.
 * Elle fournit les fonctionnalités communes et établit la structure de base que tous les contrôleurs
 * doivent suivre. Cette classe hérite automatiquement des fonctionnalités du framework Laravel
 * pour la gestion des requêtes HTTP, des réponses, de la validation et de l'autorisation.
 *
 * @details Fonctionnalités héritées automatiquement :
 * - Gestion des middleware de validation et d'autorisation
 * - Méthodes d'aide pour les réponses JSON et les redirections
 * - Gestion automatique de l'injection de dépendances
 * - Support des traits Laravel (ValidatesRequests, AuthorizesRequests, etc.)
 *
 * @package App\Http\Controllers
 * @version 1.0
 * @since Laravel 11.x
 * @author Équipe de développement
 * 
 * @note Tous les contrôleurs de l'application doivent hériter de cette classe.
 * @note Cette classe ne doit pas être instanciée directement car elle est abstraite.
 * 
 * @see PdfController Exemple d'implémentation concrète
 * @see DevisController Exemple d'implémentation concrète
 * @see TokenController Exemple d'implémentation concrète
 * 
 * @warning Ne pas modifier cette classe sans comprendre l'impact sur tous les contrôleurs enfants.
 */
abstract class Controller
{
    /**
     * @brief Constructeur de la classe de base Controller.
     *
     * Le constructeur peut être surchargé dans les classes enfants pour initialiser
     * des middleware spécifiques, des services ou des configurations particulières
     * à chaque contrôleur.
     *
     * @note Ce constructeur est automatiquement appelé lors de l'instanciation
     *       de toute classe enfant héritant de Controller.
     * 
     * @example
     * ```php
     * public function __construct()
     * {
     *     $this->middleware('auth');
     *     $this->middleware('verified');
     * }
     * ```
     */
    // Le constructeur peut être défini dans les classes enfants si nécessaire
}
