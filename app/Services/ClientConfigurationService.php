<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;

class ClientConfigurationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    //Utilisé pour les cases vides du modèle
    public static function createBICaseSupplementairesFile(string $client): bool
    {
        $optionFile = "{$client}/BI_Case_Supplementaires.json";

        if (Storage::disk('public')->exists($optionFile)) {
            return false; 
        }

        $defaultData = [
            "case1" => "",
            "case2" => ""
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $optionFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[OPTIONS_DEVIS] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }


     /**
     * Crée le fichier Options_BI.json avec une structure par défaut s'il n'existe pas.
     * * @param string $client Le nom du client (dossier)
     * @return bool True si créé avec succès, False s'il existe déjà ou en cas d'erreur
     */
    public static function createBiOptionFile(string $client): bool 
    {
        $optionFile = "{$client}/Options_BI.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($optionFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "Constat"           => [""],
            "Verification"      => [""],
            "NotesParticuliere" => [""],
            "PointVigilance"    => [""]
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $optionFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[OPTIONS_BI] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }


    public static function createCerfaConfigFile(string $client): bool 
    {
        $optionFile = "{$client}/Config_Cerfa.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($optionFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "numeroAttestationCapacite" => "Un numéro d'attestation de capacité",
            "identificationControle" => "Un identificateur de controle",
            
            "nom" => $client,
            "adresse" => "",
            "siret" => "",

            "OperateurSignataireQualiter" => "Technicien",
            "controleMaterielDate" => now()->format("Y-m-d"),

            "OperateurSignataireNom" => ""
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $optionFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[CONFIG_CERFA] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Crée le fichier documents.json avec une structure par défaut s'il n'existe pas.
     * * @param string $client Le nom du client (dossier)
     * @return bool True si créé avec succès, False s'il existe déjà ou en cas d'erreur
     */
    public static function createDocumentsFile(string $client): bool 
    {
        $documentsFile = "{$client}/documents.json";

        // Si le fichier existe déjà, on ne l'écrase pas et on s'arrête là
        if (Storage::disk('public')->exists($documentsFile)) {
            return false; 
        }

        // On prépare la structure de base en PHP (tableaux)
        $defaultData = [
            "documents" => [
                [
                    "code" => "rapport_intervention",
                    "libelle" => "Rapport d'intervention"
                ]
            ]
        ];

        try {
            // On transforme le tableau PHP en JSON bien formaté et on sauvegarde
            $stored = Storage::disk('public')->put(
                $documentsFile,
                json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return $stored; // Retourne true si l'écriture s'est bien passée

        } catch (\Exception $e) {
            \Log::error("[DOCUMENTS_FILE] Erreur de création pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }

    public static function getBiCaseSupplementaires(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/BI_Case_Supplementaires.json";
        if (!Storage::disk('public')->exists($fileName)) return [];


        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            //On default les cases vides si elles n'existent pas dans le fichier JSON
            return array_merge([
                'case1' => '',
                'case2' => '',
                'case3' => '',
                'case4' => '',
                'case5' => '',
            ], $data);

        } catch (\Exception $e) {
            \Log::error("Erreur BI_Case_Supplementaires : " . $e->getMessage());
            return [];
        }
    }


    public static function getOptionsBI(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Options_BI.json";

        if (!Storage::disk('public')->exists($fileName)) {
            return [];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            // array_values garantit que même si le JSON est {"a": [], "b": []},
            // le résultat sera [[], []] (utilisable avec [0], [1]...)
            return array_values($data);

        } catch (\Exception $e) {
            \Log::error("Erreur Options_BI : " . $e->getMessage());
            return [];
        }
    }

    public static function getOptionsBIAsMap(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Options_BI.json";

        if (!Storage::disk('public')->exists($fileName)) {
            // On retourne la structure par défaut si le fichier n'existe pas encore
            return [
                "Constat" => [],
                "Verification" => [],
                "NotesParticuliere" => [],
                "PointVigilance" => []
            ];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            // On s'assure de bien retourner un tableau associatif
            return is_array($data) ? $data : [];

        } catch (\Exception $e) {
            \Log::error("Erreur getOptionsBIAsMap : " . $e->getMessage());
            return [];
        }
    }

    public static function getConfigCerfa(string $client): array
    {
        if (empty($client)) return [];

        $fileName = "{$client}/Config_Cerfa.json";

        if (!Storage::disk('public')->exists($fileName)) {
            return [];
        }

        try {
            $content = Storage::disk('public')->get($fileName);
            $data = json_decode($content, true);

            if (!is_array($data)) return [];

            return $data;

        } catch (\Exception $e) {
            \Log::error("Erreur Config_Cerfa : " . $e->getMessage());
            return [];
        }
    }

     public static function updateOptionsBI(string $client, array $newConfig): bool
    {
        if (empty($client) || empty($newConfig)) {
            return false;
        }

        $fileName = "{$client}/Options_BI.json";
        $existingConfig = self::getOptionsBIAsMap($client);

        // array_merge écrase les anciennes valeurs par les nouvelles
        $updatedConfig = array_merge($existingConfig, $newConfig);

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($updatedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {
            \Log::error("Erreur Options_BI : " . $e->getMessage());
            return false;
        }
    }

    public static function updateConfigCerfa(string $client, array $newConfig): bool
    {
        if (empty($client) || empty($newConfig)) {
            return false;
        }

        $fileName = "{$client}/Config_Cerfa.json";
        $existingConfig = self::getConfigCerfa($client);

        // array_merge va écraser les valeurs de $existingConfig par celles de $newConfig
        // uniquement pour les clés qui sont présentes dans $newConfig.
        // C'est beaucoup plus propre et dynamique !
        $updatedConfig = array_merge($existingConfig, $newConfig);

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($updatedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour du Config_Cerfa pour le client {$client}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update "Documents.json" 
     * @param string $client - Le nom du client
     * @param array $documents - Liste de document
     */
    public static function updateDocumentsFile(string $client, array $documents): bool
    {
        if (empty($client)) {
            return false;
        }

        $fileName = "{$client}/documents.json";

        $data = [
            "documents" => array_values($documents)
        ];

        try {
            return Storage::disk('public')->put(
                $fileName,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Exception $e) {

            \Log::error("Erreur replace documents {$client}: " . $e->getMessage());

            return false;
        }
    }

}
