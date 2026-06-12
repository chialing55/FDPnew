<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ResolvesActorAccount
{
    protected function actorAccount(?Request $request = null, string $fallback = 'system'): string
    {
        $authUser = $request?->user() ?? Auth::user();

        return (string) (
            $authUser?->account
            ?? $authUser?->name
            ?? ($authUser?->id ? (string) $authUser->id : null)
            ?? $request?->input('user')
            ?? $fallback
        );
    }
}
