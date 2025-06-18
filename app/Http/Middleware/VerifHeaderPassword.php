<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifHeaderPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('secret-token')) {
            return response()->json(['error' => 'No secret token provided.'], 403);
        }

        $secretToken = config("secrets.$request->organisation_id") ?? "None";
        $adminToken = config('secrets.admin');

        $providedToken = $request->header('secret-token');

        if (!hash_equals($providedToken, $secretToken) && !hash_equals($providedToken, $adminToken)) {
            return response()->json(['error' => 'Not authorized.'], 401);
        }
        
        return $next($request);
    }
}
