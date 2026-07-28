<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil para el usuario autenticado.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $this->obtenerUsuario(Auth::id()),
        ]);
    }

    /**
     * Procesa la actualización de los datos del perfil.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);

        $dataForDatabase = collect($validated)->except(['profile_photo', 'horario_file'])->toArray();

        // Manejo de la foto de perfil
        if ($request->hasFile('profile_photo')) {
            // Eliminar foto anterior si existe
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $dataForDatabase['profile_photo_path'] = $path;
        } elseif ($request->input('profile_photo_cleared')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $dataForDatabase['profile_photo_path'] = null;
        } else {
            // Mantener la foto existente solo si hay una guardada (no vacía)
            if (!empty($user->profile_photo_path)) {
                $dataForDatabase['profile_photo_path'] = $user->profile_photo_path;
            }
        }

        // Manejo del archivo de horario
        if ($request->hasFile('horario_file')) {
            if ($user->horario_path && Storage::disk('public')->exists($user->horario_path)) {
                Storage::disk('public')->delete($user->horario_path);
            }
            $path = $request->file('horario_file')->store('horarios', 'public');
            $dataForDatabase['horario_path'] = $path;
        } else {
            if (!empty($user->horario_path)) {
                $dataForDatabase['horario_path'] = $user->horario_path;
            }
        }

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $dataForDatabase['email_verified_at'] = null;
        }

        $isPaciente = $user->role === 'paciente';
        $isPsicologo = $user->role === 'psicologo';

        if ($isPaciente || $isPsicologo) {
            if (!$user->profile_completed) {
                $camposRequeridos = ['nombres', 'apellidos', 'cedula', 'genero', 'telefono', 'ubicacion', 'discapacidad', 'tiene_hijos', 'estado_civil'];
                if ($isPaciente) {
                    $camposRequeridos[] = 'perfil_academico';
                }
                $completo = collect($camposRequeridos)->every(function ($campo) use ($dataForDatabase, $user) {
                    return !empty($dataForDatabase[$campo] ?? ($user->$campo ?? null));
                });
                if ($completo) {
                    $dataForDatabase['profile_completed'] = 1;
                }
            }
        }

        $dataForDatabase['updated_at'] = now();

        // Actualizar el perfil
        $this->actualizarPerfilUsuario($userId, $dataForDatabase);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Desactiva o elimina la cuenta del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $userId = Auth::id();

        Auth::logout();

        $this->eliminarUsuarioPermanentemente($userId);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Descarga o muestra el archivo de horario del usuario directamente.
     */
    public function downloadHorario()
    {
        $user = Auth::user();
        if ($user && $user->horario_path && Storage::disk('public')->exists($user->horario_path)) {
            return Storage::disk('public')->response($user->horario_path);
        }
        abort(404, 'Archivo no encontrado.');
    }
}
