<?php

namespace App\Console\Commands;

use App\Models\Api_Client;
use Illuminate\Console\Command;

class CreateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:create-api-key {entreprise : Le nom de l\'entreprise}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée une clés api pour une entreprise donnée et affiche la clé en clair pour l\'administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $entreprise = $this->argument('entreprise');

        if (empty($entreprise)) {
            $this->error('Le nom de l\'entreprise est requis pour créer une clé API.');
            return Command::FAILURE;
        }

        if (Api_Client::where('entreprise', $entreprise)->exists()) {
            $this->error("Une clé API existe déjà pour l'entreprise '{$entreprise}'. Veuillez choisir un nom d'entreprise différent ou supprimer l'existant.");
            return Command::FAILURE;
        }

        // Créer la clé API pour l'entreprise donnée
        $plainToken = Api_Client::generateKey($entreprise);

        // Afficher la clé en clair
        $this->info("Clé API créée pour l'entreprise '$entreprise': $plainToken");
    }
}

