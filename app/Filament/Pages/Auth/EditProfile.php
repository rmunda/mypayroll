<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    public function getTitle(): string | Htmlable
    {
        return 'My Profile — MyPayroll';
    }

    public function getHeading(): string | Htmlable
    {
        return 'My Profile';
    }
}
