<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaySlip;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaySlipPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaySlip');
    }

    public function view(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('View:PaySlip');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaySlip');
    }

    public function update(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('Update:PaySlip');
    }

    public function delete(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('Delete:PaySlip');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PaySlip');
    }

    public function restore(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('Restore:PaySlip');
    }

    public function forceDelete(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('ForceDelete:PaySlip');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaySlip');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaySlip');
    }

    public function replicate(AuthUser $authUser, PaySlip $paySlip): bool
    {
        return $authUser->can('Replicate:PaySlip');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaySlip');
    }

}