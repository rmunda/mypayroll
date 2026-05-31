<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LeaveTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveTransaction');
    }

    public function view(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('View:LeaveTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveTransaction');
    }

    public function update(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('Update:LeaveTransaction');
    }

    public function delete(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('Delete:LeaveTransaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LeaveTransaction');
    }

    public function restore(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('Restore:LeaveTransaction');
    }

    public function forceDelete(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('ForceDelete:LeaveTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveTransaction');
    }

    public function replicate(AuthUser $authUser, LeaveTransaction $leaveTransaction): bool
    {
        return $authUser->can('Replicate:LeaveTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveTransaction');
    }

}