<?php

// app/Http/Middleware/ForcePasswordReset.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordReset
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->force_password_reset) {
            // 允許進更換密碼頁，避免無限迴圈
            if (! $request->routeIs('admin.password.force.*') && ! $request->routeIs('logout')) {
                return redirect()->route('admin.password.force.edit');
            }
        }

        return $next($request);
    }
}
