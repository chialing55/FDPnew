<?php

namespace App\Http\Middleware;

use App\Support\EnsuresAdmin;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    use EnsuresAdmin;

    public function handle(Request $request, Closure $next)
    {
        $this->ensureAdmin();

        return $next($request);
    }
}
