<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FinancialYear;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancialYearPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FinancialYear');
    }

    public function view(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('View:FinancialYear');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FinancialYear');
    }

    public function update(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('Update:FinancialYear');
    }

    public function delete(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('Delete:FinancialYear');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FinancialYear');
    }

    public function restore(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('Restore:FinancialYear');
    }

    public function forceDelete(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('ForceDelete:FinancialYear');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FinancialYear');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FinancialYear');
    }

    public function replicate(AuthUser $authUser, FinancialYear $financialYear): bool
    {
        return $authUser->can('Replicate:FinancialYear');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FinancialYear');
    }

}