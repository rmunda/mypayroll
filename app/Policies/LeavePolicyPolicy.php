<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LeavePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeavePolicy');
    }

    public function view(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('View:LeavePolicy');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeavePolicy');
    }

    public function update(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('Update:LeavePolicy');
    }

    public function delete(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('Delete:LeavePolicy');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LeavePolicy');
    }

    public function restore(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('Restore:LeavePolicy');
    }

    public function forceDelete(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('ForceDelete:LeavePolicy');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeavePolicy');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeavePolicy');
    }

    public function replicate(AuthUser $authUser, LeavePolicy $leavePolicy): bool
    {
        return $authUser->can('Replicate:LeavePolicy');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeavePolicy');
    }

}