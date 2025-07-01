<?php

namespace App\Policies;

use App\Models\TestSheet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TestSheetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TestSheet $testSheet)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TestSheet $testSheet)
    {
        return $user->role == 'root_admin' || $testSheet->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TestSheet $testSheet)
    {
        return $user->role == 'root_admin' || $testSheet->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TestSheet $testSheet)
    {
        return $user->role == 'root_admin' || $testSheet->user_id == $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TestSheet $testSheet)
    {
        return $user->role == 'root_admin' || $testSheet->user_id == $user->id;
    }

    public function deleteAny(User $user)
    {
        return $user->role == 'root_admin';
    }
}
