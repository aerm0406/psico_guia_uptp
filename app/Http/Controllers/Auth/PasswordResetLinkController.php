<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $user = \App\Models\User::obtenerUsuarioPorEmail($email);

        if (!$user) {
            return back()->withErrors(['email' => 'No podemos encontrar un usuario con ese correo electrónico.']);
        }

        $token = \Illuminate\Support\Str::random(60);

        \App\Models\User::crearTokenRecuperacion($email, $token);

        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
        ], false));

        \Illuminate\Support\Facades\Mail::send('auth.emails.reset', ['url' => $resetUrl, 'name' => trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''))], function($message) use ($email) {
            $message->to($email);
            $message->subject('Restablecer contraseña');
        });

        return back()->with('status', 'Le hemos enviado por correo el enlace para restablecer su contraseña.');
    }
}
