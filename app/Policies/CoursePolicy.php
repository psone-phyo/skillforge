<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CoursePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Course');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Course');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Course');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Course');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Course');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Course');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Course');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Course');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Course');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Course');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Course');
    }

}
