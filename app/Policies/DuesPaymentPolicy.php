<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DuesPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DuesPayment');
    }

    public function view(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('View:DuesPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DuesPayment');
    }

    public function update(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('Update:DuesPayment');
    }

    public function delete(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('Delete:DuesPayment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DuesPayment');
    }

    public function restore(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('Restore:DuesPayment');
    }

    public function forceDelete(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('ForceDelete:DuesPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DuesPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DuesPayment');
    }

    public function replicate(AuthUser $authUser, DuesPayment $duesPayment): bool
    {
        return $authUser->can('Replicate:DuesPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DuesPayment');
    }

}