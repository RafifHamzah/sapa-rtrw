<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Dues;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Dues');
    }

    public function view(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('View:Dues');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Dues');
    }

    public function update(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('Update:Dues');
    }

    public function delete(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('Delete:Dues');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Dues');
    }

    public function restore(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('Restore:Dues');
    }

    public function forceDelete(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('ForceDelete:Dues');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Dues');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Dues');
    }

    public function replicate(AuthUser $authUser, Dues $dues): bool
    {
        return $authUser->can('Replicate:Dues');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Dues');
    }

}