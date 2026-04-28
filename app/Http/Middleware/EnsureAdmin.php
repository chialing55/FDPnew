<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless((int) ($request->user()?->is_admin ?? 0) === 1, 403);

        return $next($request);
    }
}
