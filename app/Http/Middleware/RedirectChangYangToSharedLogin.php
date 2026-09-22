<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class RedirectChangYangToSharedLogin extends FilamentAuthenticate
{
    /**
     * Use the application's single sign-in screen for the ChangYang panel.
     */
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
