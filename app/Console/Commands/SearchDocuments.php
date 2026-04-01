<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Documentation d'utilisation CLI (Command Line Interface)
|--------------------------------------------------------------------------
|
| Cette commande permet d'interroger la table 'token_links' et d'inspecter
| le contenu des fichiers JSON associés.
|
| Syntaxe de base :
| php artisan docs:search {type} [--search="texte"] [--valid]
|
| Exemples pratiques :
| 
| 1. Lister tous les documents de type "devis" :
|    php artisan docs:search devis
|
| 2. Lister les documents de type "cerfa" dont le lien n'a pas expiré :
|    php artisan docs:search cerfa --valid
|
| 3. Trouver un "devis" contenant le nom "Entreprise ABC" dans son JSON :
|    php artisan docs:search devis --search="Entreprise ABC"
|
| 4. Combiner la recherche interne et la validité du token :
|    php artisan docs:search devis --search="1500.00" --valid
|
*/

class SearchDocuments extends Command
{


    protected $signature = 'docs:search 
                            {type : Le type de document exact (ex: devis, cerfa)} 
                            {--search= : Chaîne de caractères à rechercher dans le contenu du fichier JSON}
                            {--valid : Restreindre la recherche aux liens dont la date d\'expiration n\'est pas dépassée}';

    protected $description = 'Recherche des documents par type en base de données et inspecte optionnellement leur contenu JSON.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $searchText = $this->option('search');
        $onlyValid = $this->option('valid');

        // Initialisation de la requête de base.
        // L'utilisation d'une clause WHERE stricte sur la colonne 'documents' optimise l'exécution SQL.
        $query = DB::table('token_links')->where('documents', $type);

        // Application du filtre d'expiration si l'option --valid est invoquée.
        if ($onlyValid) {
            $query->where('expires_at', '>', now());
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            $this->warn("Aucun document de type '{$type}' n'a été trouvé dans la base de données.");
            return Command::FAILURE;
        }

        $finalResults = [];

        foreach ($results as $row) {
            // Résolution du chemin absolu du fichier.
            // Utilisation de base_path() car les données en DB commencent par 'app/public/'.
            $fullPath = base_path($row->paths); 
            $matchesSearch = true;

            if (!is_null($searchText)) {
                $matchesSearch = false; // Nécessite validation par la recherche de contenu
                
                if (File::exists($fullPath)) {
                    // Lecture du contenu brut du fichier JSON.
                    $jsonContent = File::get($fullPath);
                    
                    // Recherche insensible à la casse dans la chaîne brute du JSON.
                    // Choix technique : on évite json_decode() pour des raisons de performance, 
                    // une recherche textuelle brute via stripos() étant moins coûteuse en CPU.
                    if (stripos($jsonContent, $searchText) !== false) {
                        $matchesSearch = true;
                    }
                } else {
                    // Signalement d'une anomalie entre l'enregistrement en base et le système de fichiers.
                    $this->error("Incohérence détectée : Fichier physique introuvable à l'emplacement {$fullPath}");
                }
            }

            // Agrégation des résultats satisfaisant l'ensemble des filtres.
            if ($matchesSearch) {
                $finalResults[] = [
                    $row->id,
                    substr($row->token, 0, 15) . '...', // Troncature du token pour l'affichage terminal
                    $row->expires_at,
                    $row->paths
                ];
            }
        }

        if (empty($finalResults)) {
            $this->warn("Aucun fichier de type '{$type}' ne correspond à la recherche '{$searchText}'.");
            return Command::FAILURE;
        }

        // Restitution des données sous forme tabulaire.
        $this->info("Trouvé " . count($finalResults) . " résultat(s) :");
        
        $this->table(
            ['ID', 'Token partiel', 'Date d\'expiration', 'Chemin du fichier'],
            $finalResults
        );

        return Command::SUCCESS;
    }
}