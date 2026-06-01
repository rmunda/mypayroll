<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TaxService::class);
        $this->app->singleton(\App\Services\PayrollService::class);
        $this->app->singleton(\App\Services\PdfService::class);
        $this->app->singleton(\App\Services\LeaveBalanceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Added afterwards

        // Override some policy for employee
        Gate::before(function ($user, string $ability) {

            $allowedFallbacks = [
                'Attendance',
                'Leave',
                'PaySlip',
            ];

            if (str_starts_with($ability, 'ViewAny:')) {

                $resource = str_replace('ViewAny:', '', $ability);

                if (
                    in_array($resource, $allowedFallbacks) &&
                    $user->hasPermissionTo("View:$resource")
                ) {
                    return true;
                }
            }

            return null;
        });
    }
}
