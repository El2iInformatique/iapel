<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class layou_client
 * @brief Modèle de gestion des layouts personnalisés par client.
 *
 * Ce modèle gère les configurations de mise en page spécifiques à chaque client
 * pour la génération des rapports d'intervention et documents personnalisés.
 * Il permet d'adapter l'apparence et le contenu des formulaires selon les
 * préférences et la charte graphique de chaque entreprise cliente.
 * 
 * Fonctionnalités principales :
 * - Stockage des paramètres de layout par client.
 * - Personnalisation de l'apparence des rapports d'intervention.
 * - Configuration des éléments visuels spécifiques (logos, couleurs, etc.).
 * - Adaptation des formulaires aux besoins métier de chaque client.
 * - Support de la personnalisation multi-client.
 *
 * @package App\Models
 * @version 1.0
 * @author Maxime ENTZ
 * @since 1.0
 * @note Utilise la table "layout_client" en base de données.
 * @note Ne surcharge pas les timestamps Laravel par défaut.
 * @warning Ce modèle est utilisé pour la personnalisation visuelle et ne doit pas contenir de données sensibles.
 * @see BiController::show() Méthode qui utilise ce modèle pour personnaliser l'affichage des rapports.
 * @example
 * ```php
 * // Récupération du layout d'un client
 * $clientLayout = layou_client::where('nom_client', 'Entreprise1')->first();
 * 
 * if ($clientLayout) {
 *     // Utilisation du layout personnalisé
 *     return view('bi', compact('data', 'client_layout'));
 * } else {
 *     // Utilisation du layout par défaut
 *     return view('bi', compact('data'));
 * }
 * ```
 */
class layou_client extends Model
{
    /**
     * @brief Nom de la table de base de données.
     * 
     * Spécifie le nom de la table utilisée pour stocker les layouts clients.
     * Cette table contient les paramètres de personnalisation pour chaque client.
     * 
     * @var string Nom de la table "layout_client".
     */
    public $table = 'layout_client';

    /**
     * @brief Les attributs qui peuvent être assignés en masse.
     * 
     * Bien que non explicitement définis, cette classe hérite des attributs
     * de base d'Eloquent. Les champs typiques incluent :
     * - nom_client : Nom du client pour lequel le layout est défini
     * - logo_path : Chemin vers le logo personnalisé
     * - couleur_principale : Couleur principale de la charte graphique
     * - couleur_secondaire : Couleur secondaire
     * - style_css : CSS personnalisé supplémentaire
     * - configuration_json : Configuration avancée au format JSON
     * 
     * @note Les attributs exacts dépendent de la structure de la table layout_client.
     * @note Pour des raisons de sécurité, il est recommandé de définir explicitement $fillable.
     */

    /**
     * @brief Récupère le layout d'un client par son nom.
     *
     * Méthode utilitaire pour récupérer facilement la configuration de layout
     * d'un client spécifique à partir de son nom.
     *
     * @param string $nomClient Nom du client dont on veut récupérer le layout.
     * 
     * @return layou_client|null Instance du layout du client ou null si non trouvé.
     * 
     * @example
     * ```php
     * $layout = layou_client::getLayoutByClient('MonEntreprise');
     * 
     * if ($layout) {
     *     echo "Layout trouvé pour " . $layout->nom_client;
     * } else {
     *     echo "Aucun layout personnalisé pour ce client";
     * }
     * ```
     * 
     * @static
     */
    public static function getLayoutByClient($nomClient)
    {
        return self::where('nom_client', $nomClient)->first();
    }

    /**
     * @brief Vérifie si un client a un layout personnalisé.
     *
     * Méthode utilitaire pour vérifier rapidement si un client
     * dispose d'une configuration de layout personnalisée.
     *
     * @param string $nomClient Nom du client à vérifier.
     * 
     * @return bool True si le client a un layout personnalisé, false sinon.
     * 
     * @example
     * ```php
     * if (layou_client::hasCustomLayout('MonEntreprise')) {
     *     // Utiliser le layout personnalisé
     *     $layout = layou_client::getLayoutByClient('MonEntreprise');
     * } else {
     *     // Utiliser le layout par défaut
     *     $layout = null;
     * }
     * ```
     * 
     * @static
     */
    public static function hasCustomLayout($nomClient)
    {
        return self::where('nom_client', $nomClient)->exists();
    }
}
