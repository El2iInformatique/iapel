<?php

namespace App\Http\Controllers;

use App\Models\Api_Client;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ConfigController extends Controller
{

    // Affiche la configuration (ou la page de connexion)
    public function show(Request $request)
    {
        $entreprise = $request->query('entreprise');
        $document = $request->query('document');

        // Vérification : Est-ce que le temps est écoulé (150s) ?
        $isExpired = session()->has('auth_expires_at') && now()->timestamp > session('auth_expires_at');

        // 2. VERIFICATION : La session a-t-elle la bonne entreprise ET n'est-elle pas expirée ?
        if (session('auth_entreprise') !== $entreprise || $isExpired) {
            
            // Si la session est expirée, on nettoie tout proprement et on prépare un message
            if ($isExpired) {
                session()->forget(['auth_entreprise', 'auth_expires_at']);
                session()->flash('error', '⏱️ Votre session a expiré après 10 minutes. Veuillez vous reconnecter.');
            }

            // Retour à la page de Login
            return view('Config.config_login', [
                'entreprise' => $entreprise, 
                'document' => $document,
            ]);
        }

        $config = [];

        if ($document === 'rapport_intervention') {
            $config = ClientController::getOptionsBI($entreprise);
        }
        elseif ($document === 'cerfa_15497') {
            $config = ClientController::getConfigCerfa($entreprise);
        }
        else {
            $config = [];
        }

        // Si on arrive ici, l'utilisateur est auth pour CETTE entreprise précise ET dans les temps !
        $type = $document; 
        return view('Config.config', compact('entreprise', 'document', 'type', 'config'));
    }

    public function authenticate(Request $request)
    {
        $entrepriseDemandee = $request->input('entreprise');
        $document = $request->input('document');
        $apiKey = $request->input('api_key');

        $hashedKey = hash('sha256', $apiKey);
        $client = Api_Client::where('token_hash', $hashedKey)->first();

        $apiKeyHash = hash('sha512', $apiKey);

        // Admin spécial : Si la clé API correspond à celle de l'admin, on autorise direct SANS vérifier l'entreprise associée
        if ($apiKeyHash === config("secrets.admin_key")) {
            session([
                'auth_entreprise' => $entrepriseDemandee,
                'auth_expires_at' => now()->addSeconds(3600)->timestamp
            ]);

            return redirect()->to("/configuration?entreprise={$entrepriseDemandee}&document={$document}");  
        }

         // Vérification supplémentaire : Est-ce que la clé API correspond à l'entreprise demandée ET est-elle active ?

        if (!$client || !$client->is_active || strtolower($client->entreprise) !== strtolower($entrepriseDemandee)) {
            Log::warning("[AUTH CONFIG] Tentative d'accès illégitime", [
                'entreprise_demandee' => $entrepriseDemandee,
                'entreprise_client_api' => $client->entreprise ?? 'Inconnue'
            ]);
            return back()->with('error', "Cette clé API n'est pas valide pour l'entreprise $entrepriseDemandee.");
        }

        // AUTHENTIFICATION : On enregistre l'entreprise AUTORISÉE
        // ET NOUVEAU : On déclenche le chronomètre de 150 secondes !
        session([
            'auth_entreprise' => $entrepriseDemandee,
            'auth_expires_at' => now()->addSeconds(600)->timestamp
        ]);

        return redirect()->to("/configuration?entreprise={$entrepriseDemandee}&document={$document}");
    }

    public function submit(Request $request)
    {
        $entreprise = $request->input('entreprise');
        $document = $request->input('document');

        Log::info("[CONFIG] Tentative de sauvegarde des paramètres", [
            'entreprise' => $entreprise,
            'document' => $document
        ]);

        if (session('auth_entreprise') !== $entreprise) {
            return back()->with('error', 'Entreprise non autorisée pour cette action.');
        }

        $isSaved = false; // On initialise une variable pour vérifier le succès

        if ($document === 'rapport_intervention') {
            $data = $request->only([
                'Constat', 'Verification', 
                'NotesParticuliere', 'PointVigilance'
            ]);
            Log::info("[CONFIG] Sauvegarde des paramètres pour BI", ['entreprise' => $entreprise]);
            $isSaved = ClientController::updateOptionsBI($entreprise, $data);
            
        } elseif ($document === 'cerfa_15497') {
            $data = $request->only([
                'nom', 'siret', 'adresse', 'numeroAttestationCapacite', 
                'identificationControle', 'controleMaterielDate', 
                'OperateurSignataireNom', 'OperateurSignataireQualiter', "OperateurSignataireNom"
            ]);

            Log::info("[CONFIG] Sauvegarde des paramètres pour CERFA 15497", ['entreprise' => $entreprise]);
            $isSaved = ClientController::updateConfigCerfa($entreprise, $data);
            
        } elseif ($document === 'devis') {
            Log::info("[CONFIG] Sauvegarde des paramètres pour Devis", ['entreprise' => $entreprise]);
            // $isSaved = ClientController::updateConfigDevis($entreprise, $data); // a implémenter quand on aura des champs spécifiques pour le devis
            $isSaved = true;
            
        } else {
            return back()->with('error', 'Type de document inconnu.');
        }

        // On gère la réponse finale en fonction du succès de l'écriture du fichier
        if ($isSaved) {
            return back()->with('success', 'La configuration a été mise à jour avec succès.');
        } else {
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de la configuration.');
        }
    }
}
