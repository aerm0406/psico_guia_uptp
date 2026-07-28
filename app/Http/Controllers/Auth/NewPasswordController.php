<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required', 
                'string', 
                'min:8', 
                'max:16', 
                'confirmed',
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/'
            ],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El formato del correo es inválido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede tener más de 16 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&).',
        ]);

        $email = $request->email;
        $token = $request->token;

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !\Illuminate\Support\Facades\Hash::check($token, $record->token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Este token de restablecimiento de contraseña es inválido.']);
        }

        \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->update([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
                'updated_at' => now(),
            ]);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        return redirect()->route('login')->with('status', '¡Su contraseña ha sido restablecida exitosamente!');
    }
}
