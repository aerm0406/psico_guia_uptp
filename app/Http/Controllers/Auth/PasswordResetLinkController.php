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
        $user = \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->where('status', 1)
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => __('passwords.user')]);
        }

        $token = \Illuminate\Support\Str::random(60);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => \Illuminate\Support\Facades\Hash::make($token),
                'created_at' => now()
            ]
        );

        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
        ], false));

        \Illuminate\Support\Facades\Mail::send('auth.emails.reset', ['url' => $resetUrl, 'name' => trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''))], function($message) use ($email) {
            $message->to($email);
            $message->subject('Restablecer contraseña');
        });

        return back()->with('status', __('passwords.sent'));
    }
}
