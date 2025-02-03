<?php

namespace App\Policies;

use App\Models\Command;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CommandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Command $command)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Command $command)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Command $command)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Command $command)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Command $command)
    {
        return $user->can('moderate')
            ? Response::allow()
            : Response::deny('You must be a moderator to access this');
    }
}
