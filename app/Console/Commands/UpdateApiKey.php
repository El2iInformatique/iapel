<?php

namespace App\Console\Commands;

use App\Models\Api_Client;
use Illuminate\Console\Command;

class UpdateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:update-api-key {entreprise : Le nom de l\'entreprise}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the API key for a client'; // Remplace par une description plus précise

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

        if (!Api_Client::where('entreprise', $entreprise)->exists()) {
            $this->error("Aucune clé API trouvée pour l'entreprise '{$entreprise}'. Veuillez vérifier le nom de l'entreprise ou créer une clé API pour celle-ci avant de tenter une mise à jour.");
            return Command::FAILURE;
        }

        // Update la clé API pour l'entreprise donnée
        $plainToken = Api_Client::updateKey($entreprise);
        // Afficher la nouvelle clé en clair
        $this->info("Clé API mise à jour pour l'entreprise '$entreprise': $plainToken");
        return Command::SUCCESS;
    }
}
