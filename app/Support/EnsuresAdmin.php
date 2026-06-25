<?php

namespace App\Support;

trait EnsuresAdmin
{
    protected function ensureAdmin(): void
    {
        abort_unless((int) (auth()->user()?->is_admin ?? 0) === 1, 403);
    }
}
