<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Support\Htmlable;

class ResetPassword extends BaseResetPassword
{
    public function getTitle(): string | Htmlable
    {
        return 'Set New Password — MyPayroll';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Set your new password';
    }
}
