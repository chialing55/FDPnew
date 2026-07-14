<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isApproved()) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = $user->isRejected()
            ? '帳號審核未通過，請洽管理員。'
            : '帳號尚未通過審核，請等待管理員審核。';

        return redirect()->route('login')->withErrors(['account' => $message]);
    }
}
