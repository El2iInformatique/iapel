<?php

namespace App\Console\Commands;

use App\Models\Token; 
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\TokenLinks; // Attention, tu es repassé sur TokenLinks ici au lieu de TokenRapport, assure-toi que c'est bien ce que tu veux !
use Illuminate\Support\Facades\File; 

/*
 * Pour utiliser cette commande il faut : 
 * Prealablement faire une sauvegarde de la base de données et des fichiers (recommandé).
 * Lancer la commande : php artisan migrate
 * Lancer la commande : php artisan export:tokens-json
 * Cette commande va :
 * - Vérifier les prérequis (existence du modèle et de la table).
 * - Faire une sauvegarde de app/public vers app/private/archive.
 * - Traiter chaque token en générant un JSON dans un dossier dédié.
 * - Gérer les doublons de dossiers en ne conservant que le meilleur candidat.
 * - Supprimer les anciens dossiers et le fichier documents.json.
 * - Afficher un résumé à la fin.
*/
 
class ExportTokensToJson extends Command
{
    protected $signature = 'export:tokens-json';
    protected $description = 'Exporte les anciens devis vers JSON, gère les doublons et nettoie.';

    public function handle()
    {
        $this->info('Vérification des prérequis...');
        
        $count = 0;
        $pdfMovedCount = 0;
        $dirDeletedCount = 0; 
        $processedDevisFiles = []; 
        $processedClients = []; // <-- Nouveau tableau pour ne vérifier documents.json qu'une fois par client

        if (!class_exists('App\\Models\\Token') || !Schema::hasTable('tokens')) {
            $this->error('Le modèle ou la table Token est introuvable.');
            return Command::FAILURE;
        }

        if (DB::table('tokens')->doesntExist()) {
            $this->info('Aucun token n\'a été trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        // --- SAUVEGARDE DE SÉCURITÉ ---
        $publicPath = storage_path('app/public');
        $archivePath = storage_path('app/private/archive/backup_' . now()->format('Y_m_d_His'));

        $this->info("Création d'une sauvegarde de app/public vers app/private/archive...");
        if (!File::exists(storage_path('app/private/archive'))) {
            File::makeDirectory(storage_path('app/private/archive'), 0755, true);
        }

        if (File::exists($publicPath)) {
            File::copyDirectory($publicPath, $archivePath);
            $this->info("Sauvegarde terminée !");
        }

        $this->info('Démarrage du traitement intensif...');

        Token::leftJoin('token_links', 'tokens.token', '=', 'token_links.token') 
            ->select('tokens.*', 'token_links.expires_at')
            ->cursor()
            ->each(function ($token) use (&$count, &$pdfMovedCount, &$dirDeletedCount, &$processedDevisFiles, &$processedClients) {
                
                try {
                    $clientName = trim($token->organisation_id ?: 'client-inconnu');
                    $docType    = "devis";
                    $devisId    = trim($token->devis_id ?: 'sans-devis');

                    // --- SUPPRESSION DU FICHIER documents.json (Vérifié 1 seule fois par client) ---
                    if (!in_array($clientName, $processedClients)) {
                        $clientPath = storage_path("app/public/{$clientName}");
                        $docJsonPath = $clientPath . '/documents.json';
                        
                        if (File::exists($docJsonPath)) {
                            File::delete($docJsonPath);
                        }
                        // On marque ce client comme traité pour ne plus chercher ce fichier
                        $processedClients[] = $clientName; 
                    }

                    // Maintien des anciens liens
                    $tokenPath = "app/public/{$clientName}/{$docType}/{$devisId}/{$devisId}.json";
                    TokenLinks::updateOrCreate(
                        ['token' => $token->token], 
                        [
                            'paths' => $tokenPath,
                            'expires_at' => $token->expires_at ?? now()->addDays(30),
                            'created_at' => now(),
                            'documents' => 'devis',
                        ]
                    );

                    $devisKey = "{$clientName}_{$devisId}";
                    if (in_array($devisKey, $processedDevisFiles)) {
                        return; // Devis déjà traité
                    }
                    $processedDevisFiles[] = $devisKey;

                    // --- DÉFINITION DES CHEMINS ---
                    $baseDevisPath = storage_path("app/public/{$clientName}/{$docType}");
                    $newDirPath = $baseDevisPath . "/{$devisId}";
                    
                    if (!File::exists($newDirPath)) {
                        File::makeDirectory($newDirPath, 0755, true);
                    }

                    // --- 1. GESTION DU FICHIER JSON ET DES COORDONNÉES ---
                    
                    // On encode les 4 colonnes dans une seule chaîne JSON
                    $coordsString = json_encode([
                        'x_signature' => (int) $token->x_signature,
                        'y_signature' => (int) $token->y_signature,
                        'x_date'      => (int) $token->x_date,
                        'y_date'      => (int) $token->y_date,
                    ]);

                    $jsonData = [
                        'dataToken' => [
                            'organisation_id' => $token->organisation_id,
                            'document' => 'devis', // Forcé à 'devis' selon ton modèle JSON
                            'devis_id' => $token->devis_id,
                        ],
                        'tiers' => $token->tiers,
                        'client_email' => $token->client_email,
                        'titre' => $token->titre,
                        'montant_HT' => (float) $token->montant_HT,
                        'montant_TVA' => (float) $token->montant_TVA,
                        'montant_TTC' => (float) $token->montant_TTC,
                        'coords' => $coordsString, 
                        'nb_pages' => (int) $token->nb_pages, // <-- Bien respecté ici !
                        'used' => (bool) $token->used,
                        'isMigrated' => true // <-- AJOUT DE LA VARIABLE DE MIGRATION ICI
                    ];
                    
                    File::put(
                        $newDirPath . "/{$devisId}.json", 
                        json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
                    );

                    // --- 2. TRAQUE DES DOSSIERS ---
                    if (File::exists($baseDevisPath)) {
                        $allDirs = File::directories($baseDevisPath);
                        $candidateDirs = [];

                        foreach ($allDirs as $dir) {
                            $dirName = basename($dir);
                            if (Str::startsWith($dirName, $devisId . '_')) {
                                $candidateDirs[] = $dir;
                            }
                        }

                        if (!empty($candidateDirs)) {
                            $bestDir = null;
                            $bestCertFile = null;
                            $bestPdfFile = null;
                            $bestModTime = 0;
                            $hasCertifiedWinner = false;

                            foreach ($candidateDirs as $dir) {
                                $files = File::files($dir);
                                $certFile = null;
                                $pdfFile = null;

                                foreach ($files as $file) {
                                    if ($file->getExtension() === 'pdf') {
                                        if (Str::contains(strtolower($file->getFilename()), 'certifie')) {
                                            $certFile = $file->getPathname();
                                        } else {
                                            $pdfFile = $file->getPathname();
                                        }
                                    }
                                }

                                $modTime = 0;
                                if ($certFile) $modTime = filemtime($certFile);
                                elseif ($pdfFile) $modTime = filemtime($pdfFile);

                                if ($certFile) {
                                    if (!$hasCertifiedWinner || $modTime > $bestModTime) {
                                        $bestDir = $dir;
                                        $bestCertFile = $certFile;
                                        $bestPdfFile = $pdfFile;
                                        $hasCertifiedWinner = true;
                                        $bestModTime = $modTime;
                                    }
                                } else {
                                    if (!$hasCertifiedWinner && $modTime > $bestModTime) {
                                        $bestDir = $dir;
                                        $bestCertFile = $certFile;
                                        $bestPdfFile = $pdfFile;
                                        $bestModTime = $modTime;
                                    }
                                }
                            }

                            // --- 3. DÉPLACEMENT DU GAGNANT ---
                            if ($bestDir) {
                                $destPdf = $newDirPath . "/{$devisId}.pdf";
                                $destCert = $newDirPath . "/{$devisId}_certifie.pdf";

                                if ($bestPdfFile) {
                                    if (File::exists($destPdf)) File::delete($destPdf); 
                                    File::move($bestPdfFile, $destPdf);
                                    $pdfMovedCount++;
                                }

                                if ($bestCertFile) {
                                    if (File::exists($destCert)) File::delete($destCert); 
                                    File::move($bestCertFile, $destCert);
                                    $pdfMovedCount++;
                                }
                            }

                            // --- 4. EXTERMINATION DE TOUS LES ANCIENS DOSSIERS ---
                            foreach ($candidateDirs as $dir) {
                                if (File::exists($dir)) {
                                    File::deleteDirectory($dir);
                                    $dirDeletedCount++;
                                }
                            }
                        }
                    }
                    
                    $count++;
                    if ($count % 500 == 0) {
                        $this->line("{$count} devis uniques traités...");
                    }

                } catch (\Exception $e) {
                    $this->error("Erreur sur le token {$token->token} (Devis: {$token->devis_id}) : " . $e->getMessage());
                }
            });

        $this->info("Opération terminée avec succès !");
        $this->line("Devis uniques traités (JSON générés) : {$count}");
        $this->line("Fichiers PDF conservés et déplacés : {$pdfMovedCount}");
        $this->line("Anciens dossiers supprimés (incluant les doublons nettoyés) : {$dirDeletedCount}");

        return Command::SUCCESS;
    }
}

