<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Rutas que están exentas de la verificación (perfil, autenticación, logout).
     */
    protected array $except = [
        'profile.complete',
        'profile.complete.store',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'logout',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'password.confirm',
        'password.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Solo aplica a pacientes o psicólogos autenticados con perfil incompleto
        if (
            $user &&
            ($user->role === 'paciente' || $user->role === 'psicologo') &&
            (!$user->profile_completed || $user->must_change_password) &&
            !in_array($request->route()?->getName(), $this->except)
        ) {
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}
