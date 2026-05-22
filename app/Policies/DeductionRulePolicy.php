<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DeductionRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeductionRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DeductionRule');
    }

    public function view(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('View:DeductionRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DeductionRule');
    }

    public function update(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('Update:DeductionRule');
    }

    public function delete(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('Delete:DeductionRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DeductionRule');
    }

    public function restore(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('Restore:DeductionRule');
    }

    public function forceDelete(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('ForceDelete:DeductionRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DeductionRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DeductionRule');
    }

    public function replicate(AuthUser $authUser, DeductionRule $deductionRule): bool
    {
        return $authUser->can('Replicate:DeductionRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DeductionRule');
    }

}