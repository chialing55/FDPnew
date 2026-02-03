<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserScope
{
    /**
     * 用法：->middleware('scope:fushan,tree')
     *      ->middleware('scope:shoushan,plot')
     */
    public function handle(Request $request, Closure $next, string $site, string $module)
    {
        $user = $request->user();

        // 未登入就導回登入頁（你的後台登入）
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // 沒權限 → 回工作選擇頁（你想要的 UX）
        if (!$user->canScope($site, $module)) {
            return redirect()
                ->route('admin.choice')
                ->with('error', '你沒有權限進入此工作項目。');
            // 或你想要標準作法：abort(403);
        }

        return $next($request);
    }
}
