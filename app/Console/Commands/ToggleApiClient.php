<?php

namespace App\Console\Commands;

use App\Models\Api_Client;
use Illuminate\Console\Command;

class ToggleApiClient extends Command
{
    /**
     * Le nom de la commande.
     * On a remplacé {id} par {identifier} pour accepter les deux formats.
     */
    protected $signature = 'api:toggle-client {identifier : L\'ID ou le nom de l\'entreprise}';

    /**
     * La description de la commande.
     */
    protected $description = 'Active ou désactive un client API via son ID ou le nom de l\'entreprise';

    public function handle()
    {
        $identifier = $this->argument('identifier');

        // Cherche le client selon le type d'identifiant passé
        if (is_numeric($identifier)) {
            // Si c'est un nombre, on cherche par ID
            $client = Api_Client::find($identifier);
        } else {
            // Si c'est du texte, on cherche par le nom de l'entreprise.
            $client = Api_Client::where('entreprise', $identifier)->first();
        }

        // Si on ne trouve rien
        if (!$client) {
            $this->error("❌ Aucun client API trouvé pour : {$identifier}");
            return Command::FAILURE;
        }

        // On inverse le statut
        $client->is_active = !$client->is_active;
        
        // On sauvegarde (ce qui déclenche le vidage du cache via le booted() du modèle)
        $client->save(); 

        // Affichage du résultat
        $statusTexte = $client->is_active ? 'ACTIVÉ' : 'DÉSACTIVÉ';
        // J'utilise $client->name (ou la colonne que tu as) pour un retour plus clair
        $nomClient = $client->name ?? "ID " . $client->id; 
        
        if ($client->is_active) {
            $this->info("✅ L'entreprise '{$nomClient}' a été {$statusTexte} avec succès.");
        } else {
            $this->warn("⚠️ L'entreprise '{$nomClient}' a été {$statusTexte} avec succès.");
        }

        return Command::SUCCESS;
    }
}

