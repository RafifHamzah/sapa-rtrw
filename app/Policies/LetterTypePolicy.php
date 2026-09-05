<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LetterType;
use Illuminate\Auth\Access\HandlesAuthorization;

class LetterTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LetterType');
    }

    public function view(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('View:LetterType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LetterType');
    }

    public function update(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('Update:LetterType');
    }

    public function delete(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('Delete:LetterType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LetterType');
    }

    public function restore(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('Restore:LetterType');
    }

    public function forceDelete(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('ForceDelete:LetterType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LetterType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LetterType');
    }

    public function replicate(AuthUser $authUser, LetterType $letterType): bool
    {
        return $authUser->can('Replicate:LetterType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LetterType');
    }

}