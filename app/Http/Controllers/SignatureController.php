<?php

namespace App\Http\Controllers;

use App\Models\TokenLinks;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\ExecutableFinder;

class SignatureController extends Controller
{

    // Afficher la page de signature


    public function show($token)
    {
        // Récupération des infos liées au token
        $dataToken = TokenLinks::where('token', $token)->first();

        // Si le token n'existe pas, on bloque l'accès
        if (!$dataToken) {
            Log::error("[TOKEN] TOKEN INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'action'   => 'show()'
            ]);
            abort(404, 'Accès refusé | Lien vers le devis introuvable.', ['Content-Type' => 'text/html']);
        }

        // Construire le chemin absolu du fichier JSON
        // storage_path() va transformer "app/public/Apple/devis/..." en "/chemin/vers/ton/projet/storage/app/public/Apple/devis/..."
        $filePath = storage_path($dataToken->paths);

        // Vérifier que le JSON existe physiquement
        if (!file_exists($filePath)) {
            Log::error("[JSON] FICHIER JSON INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $filePath
            ]);
            abort(404, 'Fichier introuvable');
        }

        // Lire et décoder le JSON pour construire la vue
        $data = json_decode(file_get_contents($filePath), true);

        // Extraction des variables de base (priorité au JSON, fallback sur la DB)
        $uid       = $data["dataToken"]['devis_id'] ?? "";
        $client    = $data["dataToken"]['organisation_id'] ?? "";
        $devisName = "{$uid}";


        // Gestion du PDF existant
        // Ici on utilise le chemin relatif car on interroge le disk 'public' de Laravel
        $basePath        = "{$client}/devis/{$devisName}/";
        $pdfCertifiePath = $basePath . $devisName . '_certifie.pdf';

        // Si le PDF existe, on affiche la vue de téléchargement
        if (Storage::disk('public')->exists($pdfCertifiePath)) {
            Log::info("[DEVIS] SIGNATURE EXISTANTE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'client'   => $client,
                'devis_id' => $uid,
                'chemin'   => $pdfCertifiePath,
                'statut'   => 'prêt au téléchargement'
            ]);
            $view = 'signature_download';
        } else {
            Log::info("[DEVIS] EN ATTENTE DE SIGNATURE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'client'   => $client,
                'devis_id' => $uid,
                'statut'   => 'signature requise'
            ]);
            $view = 'signature';
        }

        // Calcul de validité (Spécifique au devis)
        $now = now();
        $expirationDate = $dataToken->expires_at;

        $temps_restants = $now->diffInDays($expirationDate, false);
        $temps_restants = $temps_restants > 0 ? (int) $temps_restants : 0;

        $signable   = $now->lessThan($expirationDate) && !$dataToken->used;
        $is_expired = $now->greaterThan($expirationDate);

        // Préparation des variables finales pour la vue
        $devis_id        = $uid;
        $organisation_id = $client;
        $titre           = $data['titre'] ?? $dataToken->titre_devis ?? null;
        $montant_HT      = $data['montant_HT'] ?? $dataToken->montant_HT ?? null;
        $montant_TTC     = $data['montant_TTC'] ?? $dataToken->montant_TTC ?? null;
        $montant_TVA     = $data['montant_TVA'] ?? $dataToken->montant_TVA ?? null;

        // Retour de la vue
        return view($view, compact(
            'token',
            'organisation_id',
            'temps_restants',
            'signable',
            'devis_id',
            'titre',
            'montant_HT',
            'montant_TTC',
            'montant_TVA'
            
        ));
    }

    /**
     * @brief Signe un devis avec la signature manuscrite du client.
     * 
     * Processus en 3 étapes :
     * 1. Intègre la signature base64 du client dans le PDF
     * 2. Génère un fichier intermédiaire signé (non certifié)
     * 3. Fait signer électroniquement le PDF par un script Python (certificat)
     */
    public function sign(Request $request, $token)
    {
        Log::info("[SIGNATURE] DEBUT DU PROCESSUS", [
            'token'    => $token,
            'fonction' => __FUNCTION__,
            'fichier'  => basename(__FILE__),
            'ligne'    => __LINE__,
            'ip'       => $request->ip(),
            'type'     => 'manuscrite'
        ]);
        
        // ==========================================
        // VALIDATION DE LA REQUÊTE
        // ==========================================
        $request->validate([
            'signature' => 'required|string'
        ]);

        // Sécurité : Vérification stricte du format Base64 de l'image (évite les fichiers malveillants)
        $signatureBase64 = $request->input('signature');
        if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $signatureBase64)) {
            Log::warning("[SIGNATURE] FORMAT IMAGE INVALIDE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'ip'       => $request->ip(),
                'raison'   => 'format base64 incorrect'
            ]);
            return response()->json(['message' => 'Format de signature invalide. Seules les images sont acceptées.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ==========================================
        // VÉRIFICATION DU TOKEN ET DES DONNÉES
        // ==========================================
        $dataToken = TokenLinks::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$dataToken) {
            Log::warning("[TOKEN] TOKEN INVALIDE OU EXPIRÉ", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'ip'       => $request->ip(),
                'raison'   => 'token introuvable en base ou expiré'
            ]);
            return response()->json(['message' => 'Lien de signature invalide ou expiré.'], Response::HTTP_FORBIDDEN);
        }

        $filePath = storage_path($dataToken->paths);

        // Sécurité : On s'assure que c'est bien un fichier et qu'il existe
        if (!is_file($filePath)) {
            Log::critical("[FICHIER] JSON DE DONNEES INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $filePath,
                'ip'       => $request->ip(),
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur interne : Données du devis introuvables.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode(file_get_contents($filePath), true);

        if ($data["used"] == true) {
            Log::warning("[TOKEN] TOKEN DEJA UTILISE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'ip'       => $request->ip(),
                'raison'   => 'signature déjà effectuée'
            ]);
            return response()->json(['message' => 'Lien de signature invalide ou expiré.'], Response::HTTP_FORBIDDEN);
        }

        // ==========================================
        // PRÉPARATION DES CHEMINS ET FICHIERS
        // ==========================================
        $devisId = $data["dataToken"]["devis_id"];
        $client  = $data["dataToken"]["organisation_id"];
        $nomDoc  = "{$devisId}";
        $baseDir = "app/public/{$client}/devis/{$nomDoc}";
        
        $pdfOriginalPath = storage_path("{$baseDir}/{$nomDoc}.pdf");
        $signaturePath   = storage_path("{$baseDir}/{$nomDoc}_signature.png");
        $pdfSignePath    = storage_path("{$baseDir}/{$nomDoc}_signe.pdf");
        $pdfCertifiePath = storage_path("{$baseDir}/{$nomDoc}_certifie.pdf");

        if (!is_file($pdfOriginalPath)) {
            Log::critical("[PDF] FICHIER ORIGINAL INTROUVABLE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $pdfOriginalPath,
                'devis_id' => $devisId,
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Le document original est introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // ==========================================
        // CRÉATION DE L'IMAGE DE SIGNATURE
        // ==========================================
        try {
            // Nettoyage de l'en-tête base64 et conversion en binaire
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
            file_put_contents($signaturePath, $signatureData);
            Log::info("[SIGNATURE] IMAGE SAUVEGARDEE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $signaturePath,
                'taille'   => filesize($signaturePath) . ' bytes',
                'statut'   => 'succès'
            ]);
        } catch (\Exception $e) {
            Log::error("[SIGNATURE] ERREUR SAUVEGARDE IMAGE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $signaturePath,
                'erreur'   => $e->getMessage(),
                'code'     => $e->getCode()
            ]);
            return response()->json(['message' => 'Erreur lors de la sauvegarde de la signature.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // GÉNÉRATION DU PDF SIGNÉ (VIA FPDI)
        // ==========================================
        if (!PdfController::generateDevisPdf($pdfOriginalPath, $pdfSignePath, $signaturePath, $data)) {
            Log::error("[PDF] ERREUR GENERATION DEVIS SIGNE", [
                'token'              => $token,
                'fonction'           => __FUNCTION__,
                'fichier'            => basename(__FILE__),
                'ligne'              => __LINE__,
                'pdfOriginalPath'    => $pdfOriginalPath, 
                'pdfSignePath'       => $pdfSignePath,
                'signaturePath'      => $signaturePath,
                'devis_id'           => $devisId,
                'raison'             => 'generateDevisPdf() retourné false'
            ]);
            return response()->json(['message' => 'Erreur lors de la génération du document signé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // CERTIFICATION ÉLECTRONIQUE (PYTHON)
        // ==========================================
        $scriptPath = storage_path('app/signature/sign.py');
        
        // Recherche automatique de l'exécutable Python
        $executableFinder = new ExecutableFinder();
        $pythonPath = $executableFinder->find('python3') ?? $executableFinder->find('python');

        if (!$pythonPath) {
            Log::critical("[PYTHON] EXECUTABLE NON TROUVE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'recherche' => 'python3 ou python',
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur configuration serveur.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Log::info("[PYTHON] CERTIFICATION EN COURS", [
            'token'       => $token,
            'fonction'    => __FUNCTION__,
            'fichier'     => basename(__FILE__),
            'ligne'       => __LINE__,
            'script'      => $scriptPath, 
            'python_path' => $pythonPath,
            'entrees'     => [$pdfSignePath, $pdfCertifiePath],
            'statut'      => 'en cours'
        ]);

        try {
            // FIX WINDOWS : On récupère les variables d'environnement vitales pour Python/asyncio
            $env = [
                'SystemRoot'  => getenv('SystemRoot') ?: 'C:\\Windows',
                'SystemDrive' => getenv('SystemDrive') ?: 'C:',
                'PATH'        => getenv('PATH'),
            ];

            // On ajoute l'environnement ($env) en 3ème paramètre
            $process = new Process(
                [$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath],
                null, // Répertoire de travail par défaut
                $env  // Variables d'environnement injectées
            );
            
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            Log::info("[PDF] CERTIFICATION REUSSIE", [
                'token'         => $token,
                'fonction'      => __FUNCTION__,
                'fichier'       => basename(__FILE__),
                'ligne'         => __LINE__,
                'devis_id'      => $devisId,
                'chemin_sortie' => $pdfCertifiePath,
                'methode'       => 'python',
                'statut'        => 'succès'
            ]);

            $data["used"] = true;
            $stored = Storage::disk('public')->put(
                    $dataToken->paths, 
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // On s'assure que le fichier a bien été écrit sur le disque
            if (!$stored) {
                throw new \Exception("Échec de l'écriture du fichier JSON sur le disque.");
            }

        } catch (\Exception $e) {
            Log::critical("[PYTHON] ERREUR CERTIFICATION", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'devis_id' => $devisId,
                'erreur'   => $e->getMessage(),
                'code'     => $e->getCode(),
                'type'     => get_class($e),
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur lors de la certification sécurisée du document.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // FINALISATION ET NETTOYAGE
        // ==========================================
        // On verrouille le token en base de données

        Log::info("[TOKEN] MARQUE COMME UTILISE", [
            'token'    => $token,
            'fonction' => __FUNCTION__,
            'fichier'  => basename(__FILE__),
            'ligne'    => __LINE__,
            'devis_id' => $devisId,
            'client'   => $client,
            'statut'   => 'signature finalisée'
        ]);
        
        // Suppression silencieuse des fichiers de travail temporaires
        $deletedPdfSigne = @unlink($pdfSignePath);
        $deletedSignature = @unlink($signaturePath);
        
        Log::info("[NETTOYAGE] FICHIERS TEMPORAIRES SUPPRIMES", [
            'token'    => $token,
            'fonction' => __FUNCTION__,
            'fichier'  => basename(__FILE__),
            'ligne'    => __LINE__,
            'devis_id' => $devisId,
            'client'   => $client,
            'pdf_signe_supprime' => $deletedPdfSigne,
            'signature_supprimee' => $deletedSignature,
            'statut'   => 'nettoyage complet'
        ]);
        
        // Réponse finale pour le frontend
        return response()->json([
            'message' => 'Le document a été signé et certifié avec succès.',
            'status'  => 'success'
        ], Response::HTTP_OK);
    }

    /**
     * @brief Signe un devis avec le nom et prénom du client (signature typographique).
     * 
     * Alternative à la signature manuscrite :
     * - Génère une image de signature élégante avec le nom complet du client
     * - Puis suit le même processus que sign() : intégration + certification électronique
     */

    public function signWithFullName(Request $request, $token)
    {
        Log::info("[SIGNATURE_FULLNAME] DEBUT DU PROCESSUS", [
            'token'    => $token,
            'fonction' => __FUNCTION__,
            'fichier'  => basename(__FILE__),
            'ligne'    => __LINE__,
            'ip'       => $request->ip(),
            'type'     => 'nom complet'
        ]);
        
        // ==========================================
        // VALIDATION DES ENTRÉES
        // ==========================================
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname'  => 'required|string|max:50'
        ]);

        // ==========================================
        // VÉRIFICATION DU TOKEN ET DES DONNÉES
        // ==========================================
        $dataToken = TokenLinks::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$dataToken) {
            Log::warning("[TOKEN] TOKEN INVALIDE OU EXPIRÉ (FULLNAME)", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'ip'       => $request->ip(),
                'raison'   => 'token introuvable en base ou expiré'
            ]);
            return response()->json(['message' => 'Lien de signature invalide ou expiré.'], Response::HTTP_FORBIDDEN);
        }

        $filePath = storage_path($dataToken->paths);

        if (!is_file($filePath)) {
            Log::critical("[FICHIER] JSON DE DONNEES INTROUVABLE (FULLNAME)", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $filePath,
                'ip'       => $request->ip(),
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur interne : Données du devis introuvables.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode(file_get_contents($filePath), true);

        if ($data["used"] == true) {
            Log::warning("[TOKEN] TOKEN DEJA UTILISE (FULLNAME)", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'ip'       => $request->ip(),
                'raison'   => 'signature déjà effectuée'
            ]);
            return response()->json(['message' => 'Lien de signature invalide ou expiré.'], Response::HTTP_FORBIDDEN);
        }

        // ==========================================
        // PRÉPARATION DES CHEMINS ET FICHIERS
        // ==========================================
        $devisId = $data["dataToken"]["devis_id"];
        $client  = $data["dataToken"]["organisation_id"];
        $nomDoc  = "{$devisId}";
        $baseDir = "app/public/{$client}/devis/{$nomDoc}";
        
        $pdfOriginalPath = storage_path("{$baseDir}/{$nomDoc}.pdf");
        $signaturePath   = storage_path("{$baseDir}/{$nomDoc}_signature.png");
        $pdfSignePath    = storage_path("{$baseDir}/{$nomDoc}_signe.pdf");
        $pdfCertifiePath = storage_path("{$baseDir}/{$nomDoc}_certifie.pdf");

        // Création du dossier s'il n'existe pas
        if (!file_exists(storage_path($baseDir))) {
            mkdir(storage_path($baseDir), 0755, true);
        }

        if (!is_file($pdfOriginalPath)) {
            Log::critical("[PDF] FICHIER ORIGINAL INTROUVABLE (FULLNAME)", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $pdfOriginalPath,
                'devis_id' => $devisId,
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Le document original est introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // ==========================================
        // CRÉATION DE L'IMAGE DE SIGNATURE
        // ==========================================
        $firstname = ucfirst(strtolower(trim($request->input('firstname'))));
        $lastname  = ucfirst(strtolower(trim($request->input('lastname'))));
        
        try {
            $this->generateFullNameSignature($firstname, $lastname, $signaturePath);
            Log::info("[SIGNATURE] IMAGE GENEREE AVEC SUCCES", [
                'token'        => $token,
                'fonction'     => __FUNCTION__,
                'fichier'      => basename(__FILE__),
                'ligne'        => __LINE__,
                'chemin'       => $signaturePath,
                'prenom'       => $firstname,
                'nom'          => $lastname,
                'taille'       => filesize($signaturePath) . ' bytes',
                'statut'       => 'succès'
            ]);
        } catch (\Exception $e) {
            Log::error("[SIGNATURE] ERREUR GENERATION IMAGE", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'chemin'   => $signaturePath,
                'prenom'   => $firstname,
                'nom'      => $lastname,
                'erreur'   => $e->getMessage(),
                'code'     => $e->getCode()
            ]);
            return response()->json(['message' => 'Erreur lors de la génération de la signature.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // GÉNÉRATION DU PDF SIGNÉ (VIA MÉTHODE EXTRAITE)
        // ==========================================
        try {
            // NOTE: Si PdfController::generateDevisPdf() fait EXACTEMENT la même chose, tu peux l'utiliser à la place.
            // Sinon, utilise la méthode séparée ci-dessous.
            if (!PdfController::generateDevisPdfFullName($pdfOriginalPath, $pdfSignePath, $signaturePath, $data)) {
                Log::error("[PDF] ERREUR GENERATION DEVIS SIGNE FULLNAME", [
                    'token'              => $token,
                    'fonction'           => __FUNCTION__,
                    'fichier'            => basename(__FILE__),
                    'ligne'              => __LINE__,
                    'pdfOriginalPath'    => $pdfOriginalPath, 
                    'pdfSignePath'       => $pdfSignePath,
                    'signaturePath'      => $signaturePath,
                    'devis_id'           => $devisId,
                    'raison'             => 'generateDevisPdfFullName() retourné false'
                ]);
                return response()->json(['message' => 'Erreur lors de la génération du document signé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error("[PDF] EXCEPTION GENERATION DEVIS FULLNAME", [
                'token'              => $token,
                'fonction'           => __FUNCTION__,
                'fichier'            => basename(__FILE__),
                'ligne'              => __LINE__,
                'pdfOriginalPath'    => $pdfOriginalPath,
                'devis_id'           => $devisId,
                'erreur'             => $e->getMessage(),
                'code'               => $e->getCode(),
                'type'               => get_class($e)
            ]);
            return response()->json(['message' => 'Erreur lors de la génération du document signé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // CERTIFICATION ÉLECTRONIQUE (PYTHON)
        // ==========================================
        $scriptPath = storage_path('app/signature/sign.py');
        $executableFinder = new ExecutableFinder();
        $pythonPath = $executableFinder->find('python3') ?? $executableFinder->find('python');

        if (!$pythonPath) {
            Log::critical("[PYTHON] EXECUTABLE NON TROUVE (FULLNAME)", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'recherche' => 'python3 ou python',
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur configuration serveur.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $env = [
                'SystemRoot'  => getenv('SystemRoot') ?: 'C:\\Windows',
                'SystemDrive' => getenv('SystemDrive') ?: 'C:',
                'PATH'        => getenv('PATH'),
            ];

            $process = new Process([$pythonPath, $scriptPath, $pdfSignePath, $pdfCertifiePath], null, $env);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            Log::info("[PDF] CERTIFICATION REUSSIE FULLNAME", [
                'token'         => $token,
                'fonction'      => __FUNCTION__,
                'fichier'       => basename(__FILE__),
                'ligne'         => __LINE__,
                'devis_id'      => $devisId,
                'chemin_sortie' => $pdfCertifiePath,
                'methode'       => 'python (fullname)',
                'statut'        => 'succès'
            ]);

            // Sauvegarde de l'état "used" dans le JSON
            $data["used"] = true;
            $stored = Storage::disk('public')->put(
                $dataToken->paths, 
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            if (!$stored) {
                throw new \Exception("Échec de l'écriture du fichier JSON sur le disque.");
            }

        } catch (\Exception $e) {
            Log::critical("[PYTHON] ERREUR CERTIFICATION FULLNAME", [
                'token'    => $token,
                'fonction' => __FUNCTION__,
                'fichier'  => basename(__FILE__),
                'ligne'    => __LINE__,
                'devis_id' => $devisId,
                'erreur'   => $e->getMessage(),
                'code'     => $e->getCode(),
                'type'     => get_class($e),
                'severite' => 'critique'
            ]);
            return response()->json(['message' => 'Erreur lors de la certification sécurisée du document.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ==========================================
        // FINALISATION ET NETTOYAGE
        // ==========================================
        $deletedPdfSigne = @unlink($pdfSignePath);
        $deletedSignature = @unlink($signaturePath);
        
        Log::info("[NETTOYAGE] FICHIERS TEMPORAIRES SUPPRIMES FULLNAME", [
            'token'    => $token,
            'fonction' => __FUNCTION__,
            'fichier'  => basename(__FILE__),
            'ligne'    => __LINE__,
            'devis_id' => $devisId,
            'client'   => $client,
            'pdf_signe_supprime' => $deletedPdfSigne,
            'signature_supprimee' => $deletedSignature,
            'statut'   => 'nettoyage complet'
        ]);
        
        return response()->json([
            'message' => 'Le document a été signé et certifié avec succès.',
            'status'  => 'success'
        ], Response::HTTP_OK);
    }

    /**
     * Génère une signature élégante et professionnelle à partir du nom et prénom complets
     */
    private function generateFullNameSignature($firstname, $lastname, $outputPath)
    {
        // Dimensions de l'image optimisées pour une signature professionnelle
        $width = 1200;  // Augmenté pour meilleure qualité
        $height = 400;   // Augmenté pour meilleure qualité
        
        // Créer une image vide avec support alpha pour la transparence
        $image = imagecreatetruecolor($width, $height);
        
        // Activer le canal alpha pour la transparence
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Définir les couleurs
        $backgroundColor = imagecolorallocatealpha($image, 255, 255, 255, 127); // Transparent
        $textColor = imagecolorallocate($image, 0, 0, 0); // Noir pur pour meilleure lisibilité
        $lineColor = imagecolorallocate($image, 0, 0, 0); // Ligne noire
        $shadowColor = imagecolorallocatealpha($image, 50, 50, 50, 40); // Ombre très subtile
        
        // Remplir avec le fond transparent
        imagefill($image, 0, 0, $backgroundColor);
        
        // Liste de polices PRIORISÉE POUR UBUNTU/LINUX SERVER
        $possibleFonts = [
            // POLICE PERSONNALISÉE MANUSCRITE - PRIORITÉ ABSOLUE
            '/var/www/laravel/fonts/BrittanySignature.ttf',
            base_path('fonts/BrittanySignature.ttf'), // Chemin Laravel alternatif
            // Ubuntu/Debian - Polices par défaut les plus courantes
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Italic.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Oblique.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            // Chemins alternatifs pour certaines distributions
            '/usr/share/fonts/TTF/LiberationSerif-Italic.ttf',
            '/usr/share/fonts/TTF/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/TTF/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/TTF/DejaVuSerif.ttf',
            // Polices Ubuntu spécifiques
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-Italic.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-Regular.ttf',
            // Windows (au cas où en développement local)
            'C:/Windows/Fonts/times.ttf',
            'C:/Windows/Fonts/timesi.ttf',
            'C:/Windows/Fonts/georgia.ttf',
            'C:/Windows/Fonts/georgiai.ttf',
            // macOS (au cas où)
            '/System/Library/Fonts/Times New Roman Italic.ttf',
            '/System/Library/Fonts/Georgia Italic.ttf',
        ];
        
        $fontPath = null;
        $fontName = 'Non détectée';
        
        foreach ($possibleFonts as $fontFile) {
            if (file_exists($fontFile)) {
                $fontPath = $fontFile;
                $fontName = basename($fontFile);
                break;
            }
        }
        
        $fullName = $firstname . ' ' . $lastname;
        
        if ($fontPath && function_exists('imagettftext')) {
            // POLICE PLUS GRANDE ET PLUS LISIBLE
            $fontSize = 90; // 90pt pour excellente lisibilité
            $angle = -2; // Inclinaison très subtile pour effet élégant
            
            try {
                // Calculer les dimensions du texte pour le centrer
                $textBox = imagettfbbox($fontSize, $angle, $fontPath, $fullName);
                if ($textBox !== false) {
                    $textWidth = $textBox[4] - $textBox[0];
                    $textHeight = $textBox[1] - $textBox[5];
                    $x = ($width - $textWidth) / 2 - $textBox[0];
                    $y = ($height - $textHeight) / 2 - $textBox[5];
                } else {
                    $x = $width / 2 - (strlen($fullName) * 30);
                    $y = $height / 2 + 30;
                }
                
                // Ajouter une ombre portée très subtile pour la profondeur
                imagettftext($image, $fontSize, $angle, $x + 2, $y + 2, $shadowColor, $fontPath, $fullName);
                
                // Texte principal en noir pur - TRAIT ÉPAISSI
                imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontPath, $fullName);
                // Deuxième passage pour épaissir légèrement
                imagettftext($image, $fontSize, $angle, $x + 0.5, $y, $textColor, $fontPath, $fullName);
                
                // Ajouter une ligne décorative sous la signature
                $lineY = $y + 30;
                $lineStartX = $x;
                $lineEndX = $x + $textWidth;
                
                // Ligne principale noire - ÉPAISSIE
                $this->drawSmoothLine($image, $lineStartX, $lineY, $lineEndX, $lineY, $lineColor, 3);
                
                // Flourish décoratif au début
                $this->drawFlourishStart($image, $lineStartX - 15, $lineY, $lineColor);
                
            } catch (\Exception $e) {
                $fontPath = null;
            }
        }
        
        if (!$fontPath) {
            // Fallback : créer une signature élégante avec police système
            $this->createElegantHandwrittenSignature($image, $fullName, $textColor, $lineColor, $width, $height);
        }
        
        // Sauvegarder l'image en PNG haute qualité
        imagepng($image, $outputPath, 0);
        imagedestroy($image);
    }
    
    /**
     * Crée une signature manuscrite élégante avec un style professionnel
     */
    private function createElegantHandwrittenSignature($image, $fullName, $textColor, $lineColor, $width, $height)
    {
        // Pour le fallback, on utilise une approche avec police système plus grande
        $centerX = $width / 2;
        $centerY = $height / 2;
        
        // Dessiner le nom en style élégant - TAILLE AUGMENTÉE
        $fontSize = 5; // Police système grande taille
        $textWidth = imagefontwidth($fontSize) * strlen($fullName);
        $textHeight = imagefontheight($fontSize);
        
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        // Ombre très subtile
        $shadowColor = imagecolorallocatealpha($image, 50, 50, 50, 40);
        imagestring($image, $fontSize, $x + 2, $y + 2, $fullName, $shadowColor);
        
        // Texte principal en noir pur
        imagestring($image, $fontSize, $x, $y, $fullName, $textColor);
        
        // Ligne décorative noire
        $lineY = $y + $textHeight + 10;
        imagesetthickness($image, 2);
        imageline($image, $x, $lineY, $x + $textWidth, $lineY, $lineColor);
        imagesetthickness($image, 1);
    }
    
    /**
     * Dessine une ligne lisse avec antialiasing
     */
    private function drawSmoothLine($image, $x1, $y1, $x2, $y2, $color, $thickness)
    {
        imagesetthickness($image, $thickness);
        imageline($image, $x1, $y1, $x2, $y2, $color);
        imagesetthickness($image, 1);
    }
    
    /**
     * Dessine un flourish décoratif au début de la signature
     */
    private function drawFlourishStart($image, $x, $y, $color)
    {
        // Petite courbe décorative
        $points = [
            $x - 15, $y - 5,
            $x - 10, $y - 10,
            $x - 5, $y - 8,
            $x, $y
        ];
        
        imagesetthickness($image, 2);
        // Dessiner la courbe avec plusieurs segments
        for ($i = 0; $i < count($points) - 2; $i += 2) {
            imageline($image, $points[$i], $points[$i+1], $points[$i+2], $points[$i+3], $color);
        }
        imagesetthickness($image, 3);
    }
}