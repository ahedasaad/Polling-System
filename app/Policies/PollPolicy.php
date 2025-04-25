<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PollPolicy
{

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Poll $poll): bool
    {
        return $user->role == 'ADMIN' && $poll->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Poll $poll): bool
    {
        return $user->role == 'ADMIN' && $poll->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Poll $poll): bool
    // {
    //     //
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Poll $poll): bool
    // {
    //     //
    // }
}
