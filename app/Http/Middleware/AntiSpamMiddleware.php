<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache; // Pour stocker les tentatives de spam

class AntiSpamMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $maxAttempts  Le nombre maximum de tentatives de spam autorisées pour cette route.
     * @param  int  $decayMinutes Le nombre de minutes avant que le compteur de tentatives ne soit réinitialisé.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        // On utilise l'URL de la route et l'adresse IP de l'utilisateur.
        $key = 'anti-spam:' . $request->fullUrl() . ':' . $request->ip();

        // Cache::increment() retourne la nouvelle valeur après incrémentation.
        $attempts = Cache::increment($key);

        // Cela permet de réinitialiser le compteur après un certain temps.
        if ($attempts === 1) {
            Cache::put($key, 1, now()->addMinutes($decayMinutes));
        }

        if ($attempts > $maxAttempts) {
            // return redirect()->back()->withErrors(['spam' => 'Trop de tentatives. Veuillez réessayer plus tard.']);
            // ou
            return response('Trop de tentatives de spam. Veuillez réessayer plus tard.', 429); // 429 Too Many Requests
        }

        // 5. Si pas de spam détecté, passer la requête au middleware suivant ou au contrôleur
        return $next($request);
    }
}