<?php

namespace App\Policies;

use App\Models\User;
use App\Models\tnotificaciones;
use Illuminate\Auth\Access\Response;

class TnotificacionesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, tnotificaciones $tnotificaciones): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, tnotificaciones $tnotificaciones): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, tnotificaciones $tnotificaciones): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, tnotificaciones $tnotificaciones): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, tnotificaciones $tnotificaciones): bool
    {
        return false;
    }
}
