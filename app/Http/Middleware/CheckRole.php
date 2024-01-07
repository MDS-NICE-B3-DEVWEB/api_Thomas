<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, $role)
{
    if (!Auth::check()) // Pas d'utilisateur connecté
        return response()->json([
            'status_code' => 403,
            'error' => 'Non authentifié.',
        ], 403);

    $user = Auth::user();

    // Vérifie si l'utilisateur a le rôle requis
    if ($user->roles->pluck('name')->contains($role))
        return $next($request);

    return response()->json([
        'status_code' => 403,
        'error' => 'Accès refusé. Cette page est réservée aux ' . $role . 's.',
    ], 403);
}
}
