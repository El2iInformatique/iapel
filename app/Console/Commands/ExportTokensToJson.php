<?php

namespace App\Console\Commands;

use App\Models\Token; 
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\TokenLinks;
 
class ExportTokensToJson extends Command
{
    /**
     * Le nom et la signature de la commande.
     */
    protected $signature = 'export:tokens-json';

    /**
     * La description de la commande.
     */
    protected $description = 'Exporte les tokens vers JSON, gère les doublons et déplace les PDF depuis les anciens dossiers.';

    /**
     * Exécution de la commande.
     */
    public function handle()
    {
        $this->info('Démarrage de l\'exportation et du nettoyage des PDF...');
        $count = 0;
        $pdfMovedCount = 0;
        $pdfDeletedCount = 0; // Petit compteur pour les doublons supprimés

        $modelPath = app_path('Models/Token.php');

        if (!file_exists($modelPath)) {
            $this->error('Le fichier du modèle "Token.php" n\'existe pas sur cet environnement.');
            return Command::FAILURE;
        }

        $modelClass = 'App\\Models\\Token'; 
        if (!class_exists($modelClass)) {
            $this->error('Le fichier existe, mais la classe App\Models\Token est introuvable.');
            return Command::FAILURE;
        }

        if (!Schema::hasTable('tokens')) {
            $this->error('La table "tokens" n\'existe pas dans la base de données.');
            return Command::FAILURE;
        }

        if (DB::table('tokens')->doesntExist()) {
            $this->info('La table existe, mais aucun token n\'a été trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        Token::leftJoin('token_links', 'tokens.token', '=', 'token_links.token') 
            ->select(
                'tokens.*', 
                'token_links.paths', 
                'token_links.expires_at', 
                'token_links.documents',
                'token_links.created_at as link_created_at'
            )
            ->cursor()
            ->each(function ($token) use (&$count, &$pdfMovedCount, &$pdfDeletedCount) {
                
                $clientName = Str::slug($token->organisation_id ?: 'client-inconnu');
                $docType    = "devis";
                $devisId    = $token->devis_id ?: 'sans-devis';
                $tokenStr   = $token->token;

                // --- DÉFINITION DES CHEMINS ---
                
                $oldDirPath = storage_path("app/public/{$clientName}/{$docType}/{$devisId}_{$tokenStr}");
                $newDirPath = storage_path("app/public/{$clientName}/{$docType}/{$devisId}");
                
                if (!file_exists($newDirPath)) {
                    mkdir($newDirPath, 0755, true);
                }

                // --- 1. GESTION DU FICHIER JSON ---
                $jsonData = [
                    'dataToken' => [
                        'organisation_id' => $token->organisation_id,
                        'document' => $token->document,
                        'devis_id' => $token->devis_id,
                    ],
                    'tiers' => $token->tiers,
                    'client_email' => $token->client_email,
                    'titre' => $token->titre,
                    'montant_HT' => (float) $token->montant_HT,
                    'montant_TVA' => (float) $token->montant_TVA,
                    'montant_TTC' => (float) $token->montant_TTC,
                    'coords' => $token->coords, 
                    'nb_pages' => (int) $token->nb_pages,
                    'used' => (bool) $token->used
                ];

                $jsonFilePath = $newDirPath . "/{$devisId}.json";
                $tokenPath = "app/public/{$clientName}/{$docType}/{$devisId}/{$devisId}.json";
                
                TokenLinks::create([
                    'token' => $token->token,
                    'paths' => $tokenPath,
                    'expires_at' => $token->expires_at ?? now()->addDays(30),
                    'created_at' => now(),
                    'documents' => 'devis',
                ]);
                
                file_put_contents(
                    $jsonFilePath, 
                    json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
                );

                // --- 2. DÉPLACEMENT ET NETTOYAGE DES FICHIERS PDF ---
                
                // Noms d'origine (Correction : underscore pour _certifie.pdf)
                $originalPdfName = $devisId . '_' . $tokenStr . '.pdf';
                $originalCertPdfName = $devisId . '_' . $tokenStr . '_certifie.pdf'; 

                $sourcePdfPath = $oldDirPath . '/' . $originalPdfName;
                $sourceCertPdfPath = $oldDirPath . '/' . $originalCertPdfName;

                // Nouveaux chemins cibles (J'ai gardé _certifie ici aussi pour être cohérent avec l'existant)
                $destPdfPath = $newDirPath . "/{$devisId}.pdf";
                $destCertPdfPath = $newDirPath . "/{$devisId}_certifie.pdf";

                // Gestion du PDF classique
                if (file_exists($sourcePdfPath)) {
                    if (!file_exists($destPdfPath)) {
                        rename($sourcePdfPath, $destPdfPath);
                        $pdfMovedCount++;
                    } else {
                        // Si le fichier cible existe déjà (autre token du même devis), on supprime le doublon
                        unlink($sourcePdfPath);
                        $pdfDeletedCount++;
                    }
                }

                // Gestion du PDF certifié
                if (file_exists($sourceCertPdfPath)) {
                    if (!file_exists($destCertPdfPath)) {
                        rename($sourceCertPdfPath, $destCertPdfPath);
                        $pdfMovedCount++;
                    } else {
                        // Si le certifié existe déjà, on supprime le doublon
                        unlink($sourceCertPdfPath);
                        $pdfDeletedCount++;
                    }
                }

                // --- 3. SUPPRESSION DE L'ANCIEN DOSSIER (Toujours commenté) ---
                /*
                if (is_dir($oldDirPath)) {
                    $files = array_diff(scandir($oldDirPath), ['.', '..']);
                    if (empty($files)) {
                        rmdir($oldDirPath);
                    }
                }
                */
                
                $count++;
                
                if ($count % 1000 == 0) {
                    $this->line("{$count} tokens traités...");
                }
            });

        $this->info("Opération terminée avec succès !");
        $this->line("Dossiers/JSON générés : {$count}");
        $this->line("Fichiers PDF déplacés : {$pdfMovedCount}");
        $this->line("Fichiers PDF en doublon supprimés : {$pdfDeletedCount}");

        return Command::SUCCESS;
    }
}