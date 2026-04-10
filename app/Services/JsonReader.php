<?php

namespace App\Services;

use App\Models\TokenLinks;
use Illuminate\Support\Facades\Log;

/**
 * @class JsonReader
 * @brief Service interne de lecture de fichiers JSON.
 *
 * Classe utilitaire à appeler statiquement depuis n'importe quel contrôleur.
 * Aucune route associée — uniquement pour alléger le code dupliqué dans
 * BiController, SignatureController et PdfController.
 *
 * Utilisation :
 *   $data = JsonReader::fromToken($dataToken);
 *   $data = JsonReader::fromPath($client, $document, $uid);
 *   $data = JsonReader::fromPathNoClient($document, $uid);
 *   $data = JsonReader::documentsList($client);
 *
 * Chaque méthode retourne un tableau associatif, ou lève une \RuntimeException
 * si le fichier est introuvable — à gérer dans le contrôleur appelant.
 */
class JsonReader
{
    // =========================================================================
    //  MÉTHODE CENTRALE (privée)
    // =========================================================================

    /**
     * @brief Lit et décode un fichier JSON depuis un chemin absolu.
     *
     * Remplace partout le pattern répété :
     *   $data = json_decode(file_get_contents($filePath), true);
     *
     * @param  string $filePath  Chemin absolu sur le disque
     * @param  string $context   Nom du contrôleur appelant (pour les logs)
     * @return array             Tableau associatif issu du JSON
     *
     * @throws \RuntimeException Si le fichier est introuvable ou le JSON invalide
     */
    private static function read(string $filePath, string $context = 'JsonReader'): array
    {
        if (!file_exists($filePath)) {
            Log::error("[{$context}] Fichier JSON introuvable", ['path' => $filePath]);
            throw new \RuntimeException("Fichier JSON introuvable : {$filePath}");
        }

        $decoded = json_decode(file_get_contents($filePath), true);

        if ($decoded === null) {
            Log::error("[{$context}] JSON invalide ou vide", ['path' => $filePath]);
            throw new \RuntimeException("JSON invalide : {$filePath}");
        }

        return $decoded;
    }

    // =========================================================================
    //  API PUBLIQUE
    // =========================================================================

    /**
     * @brief Lit le JSON dont le chemin est stocké dans un enregistrement TokenLinks.
     *
     * Remplace partout le pattern :
     *   $filePath = storage_path($dataToken->paths);
     *   $data = json_decode(file_get_contents($filePath), true);
     *
     * Utilisé dans :
     *   BiController::download(), show(), submit(), delete()
     *   SignatureController::show(), saveSignature(), saveSignatureFullName()
     *   PdfController::show(), upload(), viewDevis()
     *
     * @param  TokenLinks $dataToken  Enregistrement TokenLinks récupéré en amont
     * @param  string     $context    Nom du contrôleur appelant (pour les logs)
     * @return array
     *
     * @throws \RuntimeException
     *
     * @example
     *   $dataToken = TokenLinks::where('token', $token)->first();
     *   $data = JsonReader::fromToken($dataToken);
     */
    public static function fromToken(TokenLinks $dataToken, string $context = 'JsonReader'): array
    {
        $filePath = storage_path($dataToken->paths);
        return self::read($filePath, $context);
    }

    /**
     * @brief Lit le JSON d'un document identifié par client / document / uid.
     *
     * Chemin construit : storage/app/public/{client}/{document}/{uid}/{uid}.json
     *
     * Remplace partout le pattern :
     *   $filePath = storage_path('app/public/' . $client . '/' . $document . '/' . $uid . '/' . $uid . '.json');
     *   $data = json_decode(file_get_contents($filePath), true);
     *
     * Utilisé dans :
     *   BiController::check(), show(), submit()
     *   PdfController::generateAttestationTVA(), generateBi(), generateCerfa()
     *
     * @param  string $client
     * @param  string $document
     * @param  string $uid
     * @param  string $context   Nom du contrôleur appelant (pour les logs)
     * @return array
     *
     * @throws \RuntimeException
     *
     * @example
     *   $data = JsonReader::fromPath($client, $document, $uid);
     */
    public static function fromPath(string $client, string $document, string $uid, string $context = 'JsonReader'): array
    {
        $filePath = storage_path("app/public/{$client}/{$document}/{$uid}/{$uid}.json");
        return self::read($filePath, $context);
    }

    /**
     * @brief Lit le JSON d'un document sans client dans le chemin.
     *
     * Chemin construit : storage/app/public/{document}/{uid}/{uid}.json
     *
     * Cas spécifique à PdfController::generateDownloadPDF() qui stocke
     * les documents génériques sans dossier client.
     *
     * @param  string $document
     * @param  string $uid
     * @param  string $context
     * @return array
     *
     * @throws \RuntimeException
     *
     * @example
     *   $data = JsonReader::fromPathNoClient($document, $uid);
     */
    public static function fromPathNoClient(string $document, string $uid, string $context = 'JsonReader'): array
    {
        $filePath = storage_path("app/public/{$document}/{$uid}/{$uid}.json");
        return self::read($filePath, $context);
    }

    /**
     * @brief Lit le fichier documents.json d'un client (liste de tous ses documents).
     *
     * Chemin : storage/app/public/{client}/documents.json
     *
     * Remplace le pattern dans BiController::getDocuments() :
     *   $filePath = storage_path('app/public/' . $client . '/documents.json');
     *   $json = file_get_contents($filePath);
     *   $data = json_decode($json, true);
     *
     * @param  string $client
     * @param  string $context
     * @return array
     *
     * @throws \RuntimeException
     *
     * @example
     *   $data = JsonReader::documentsList($client);
     */
    public static function documentsList(string $client, string $context = 'JsonReader'): array
    {
        $filePath = storage_path("app/public/{$client}/documents.json");
        return self::read($filePath, $context);
    }

    /**
     * @brief Retourne le chemin absolu d'un JSON sans le lire.
     *
     * Utile quand le contrôleur a besoin du chemin pour d'autres opérations
     * (ex: BiController::check() qui vérifie aussi l'existence du PDF associé).
     *
     * @param  string $client
     * @param  string $document
     * @param  string $uid
     * @return string  Chemin absolu
     *
     * @example
     *   $jsonPath = JsonReader::path($client, $document, $uid);
     *   $pdfPath  = str_replace('.json', '.pdf', $jsonPath);
     *   $exists   = file_exists($jsonPath);
     */
    public static function path(string $client, string $document, string $uid): string
    {
        return storage_path("app/public/{$client}/{$document}/{$uid}/{$uid}.json");
    }
}