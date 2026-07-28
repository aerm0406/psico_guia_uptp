<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cedula' => ['required', 'string', 'max:20', 'unique:users,cedula'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'max:16',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'role' => ['required', 'in:psicologo,paciente'],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial.',
        ]);

        $userId = $this->registrarNuevoUsuario([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);
        
        $user = $this->instanciarUsuarioParaNotificacion($userId);

        event(new Registered($user));

        if ($user->role === 'psicologo') {
            $admins = \Illuminate\Support\Facades\DB::table('users')->where('role', 'admin')->get();
            $notifications = [];
            $now = now();
            foreach ($admins as $admin) {
                $notifications[] = [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\Notifications\NewPsychologistNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $admin->id,
                    'data' => json_encode([
                        'title' => 'Nueva Solicitud de Registro',
                        'body' => 'El psicólogo ' . $user->nombres . ' ' . $user->apellidos . ' ha solicitado ingreso.',
                        'link' => route('admin.users.index'),
                        'type_id' => 'nuevo_aviso',
                    ]),
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (!empty($notifications)) {
                \Illuminate\Support\Facades\DB::table('notifications')->insert($notifications);
            }

            return redirect(route('login', absolute: false))->with('status', 'Su solicitud de registro ha sido recibida con éxito. La cual será analizada para corroborar su identidad como psicólogo de la institución. Quede atento para poder ingresar al sistema.');
        }

        Auth::loginUsingId($userId);

        if ($user->role === 'paciente') {
            return redirect(route('citas.index', absolute: false));
        }

        return redirect(route('dashboard', absolute: false));
    }
}

