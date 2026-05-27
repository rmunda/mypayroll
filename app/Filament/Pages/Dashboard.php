<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function shouldRegisterNavigation(): bool
    {
        return ! auth()->user()?->hasRole('employee');
    }

    public function mount(): void
    {
        if (auth()->user()?->hasRole('employee')) {
            redirect('/admin/pay-slips');
        }
    }
}