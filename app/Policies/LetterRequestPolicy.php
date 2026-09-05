<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LetterRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class LetterRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LetterRequest');
    }

    public function view(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('View:LetterRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LetterRequest');
    }

    public function update(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('Update:LetterRequest');
    }

    public function delete(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('Delete:LetterRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LetterRequest');
    }

    public function restore(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('Restore:LetterRequest');
    }

    public function forceDelete(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('ForceDelete:LetterRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LetterRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LetterRequest');
    }

    public function replicate(AuthUser $authUser, LetterRequest $letterRequest): bool
    {
        return $authUser->can('Replicate:LetterRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LetterRequest');
    }

}