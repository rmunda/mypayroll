<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Contracts\Support\Htmlable;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function getTitle(): string | Htmlable
    {
        return 'Forgot Password — MyPayroll';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Forgot your password?';
    }

    // Keeps the parent's "← Back to login" link as the subheading
}
