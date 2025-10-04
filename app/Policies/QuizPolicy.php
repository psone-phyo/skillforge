<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class QuizPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Quiz');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Quiz');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Quiz');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Quiz');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Quiz');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Quiz');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Quiz');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Quiz');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Quiz');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Quiz');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Quiz');
    }

}
