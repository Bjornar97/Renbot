<?php

namespace App\Policies;

use App\Models\Rule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class RulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Rule $rule)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return Gate::allows('moderate')
            ? Response::allow()
            : Response::deny('You need to be a moderator to create a new rule');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Rule $rule)
    {
        return Gate::allows('moderate')
            ? Response::allow()
            : Response::deny('You need to be a moderator to update a rule');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Rule $rule)
    {
        return Gate::allows('moderate')
            ? Response::allow()
            : Response::deny('You need to be a moderator to delete a rule');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Rule $rule)
    {
        return Gate::allows('moderate')
            ? Response::allow()
            : Response::deny('You need to be a moderator to restore a rule');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Rule $rule)
    {
        return Gate::allows('moderate')
            ? Response::allow()
            : Response::deny('You need to be a moderator to delete a rule');
    }
}
