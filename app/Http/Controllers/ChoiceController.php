<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChoiceController extends Controller
{
    public function check(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isApproved()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->isRejected()
                ? '帳號審核未通過，請洽管理員。'
                : '帳號尚未通過審核，請等待管理員審核。';

            return redirect()->route('login')->with('status', $message);
        }
        // 1) 研究工作（依權限過濾）
        // dd($user->id, $user->canScope('fushan', 'tree'), config('work'));

        $workItems = collect(config('work', []))
            // ① 排除語意型項目（all 不應該成為入口）
            ->filter(function ($w) {
                return ($w['site'] ?? '') !== 'all'
                    && ($w['module'] ?? '') !== 'all'
                    && !empty($w['route']); // 再保險一次
            })

            // ② 使用者權限判斷（user_scopes.is_enabled 已在 canScope 內）
            ->filter(function ($w) use ($user) {
                return $user->canScope($w['site'], $w['module']);
            })

            // ③ 產生實際 url
            ->map(function ($w) {
                $w['url'] = route($w['route']);
                return $w;
            })

            ->unique('key')
            ->values()
            ->all();




        // 2) 其它「不走 scope 的固定入口」（例如研究成果平台 / 網頁後台平台）


        return view('choice', [
            'authUserName' => $user->name,   // 這樣 blade 不會把 model 印出錯誤
            // 'fixedItems'   => $fixedItems,
            'workItems'    => $workItems,
        ]);
    }
}
