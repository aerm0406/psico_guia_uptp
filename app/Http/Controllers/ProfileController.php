<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil para el usuario autenticado.
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $this->obtenerUsuario(Auth::id()),
        ]);
    }

    /**
     * Procesa la actualización de los datos del perfil. 
     * Incluye lógica para sincronizar el nombre y verificar si el perfil se ha completado.
     * @param ProfileUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);

        if ($request->hasFile('horario_file')) {
            $path = $request->file('horario_file')->store('horarios', 'public');
            $validated['horario_path'] = $path;
        }

        $updateData = $validated;
        
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $updateData['email_verified_at'] = null;
        }

        $isPaciente = $user->role === 'paciente';
        $isPsicologo = $user->role === 'psicologo';

        if ($isPaciente || $isPsicologo) {
            if (!$user->profile_completed) {
                $camposRequeridos = ['nombres', 'apellidos', 'cedula', 'genero', 'telefono', 'ubicacion', 'discapacidad', 'tiene_hijos', 'estado_civil'];
                
                if ($isPaciente) {
                    $camposRequeridos[] = 'perfil_academico';
                }

                $completo = collect($camposRequeridos)->every(function ($campo) use ($updateData, $user) {
                    return !empty($updateData[$campo] ?? $user->$campo);
                });
                
                if ($completo) {
                    $updateData['profile_completed'] = 1;
                }
            }
        }

        $updateData['updated_at'] = now();

        $this->actualizarPerfilUsuario($userId, $updateData);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Desactiva o elimina la cuenta del usuario (según la lógica del sistema).
     * @param Request $request
     * @return RedirectResponse
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
}

