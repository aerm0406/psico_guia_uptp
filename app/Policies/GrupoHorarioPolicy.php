<?php

namespace App\Policies;

use App\Models\GrupoHorario;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GrupoHorarioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver sus propios grupos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GrupoHorario $grupoHorario): bool
    {
        return $user->id === $grupoHorario->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden crear grupos
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GrupoHorario $grupoHorario): bool
    {
        return $user->id === $grupoHorario->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GrupoHorario $grupoHorario): bool
    {
        return $user->id === $grupoHorario->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GrupoHorario $grupoHorario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GrupoHorario $grupoHorario): bool
    {
        return false;
    }
}
