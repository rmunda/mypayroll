<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WeeklyOffRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class WeeklyOffRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WeeklyOffRule');
    }

    public function view(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('View:WeeklyOffRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WeeklyOffRule');
    }

    public function update(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('Update:WeeklyOffRule');
    }

    public function delete(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('Delete:WeeklyOffRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WeeklyOffRule');
    }

    public function restore(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('Restore:WeeklyOffRule');
    }

    public function forceDelete(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('ForceDelete:WeeklyOffRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WeeklyOffRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WeeklyOffRule');
    }

    public function replicate(AuthUser $authUser, WeeklyOffRule $weeklyOffRule): bool
    {
        return $authUser->can('Replicate:WeeklyOffRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WeeklyOffRule');
    }

}