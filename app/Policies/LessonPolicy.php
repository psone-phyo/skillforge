<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LessonPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lesson');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Lesson');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lesson');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Lesson');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Lesson');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Lesson');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Lesson');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lesson');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lesson');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Lesson');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lesson');
    }

}
