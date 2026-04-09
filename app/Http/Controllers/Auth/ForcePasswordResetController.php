<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ForcePasswordResetController extends Controller
{

    public function edit(Request $request)
    {
        // // 如果不需要強制換密碼，就不要讓他進來
        // if (! $request->user()->force_password_reset) {
        //     return redirect()->route('dashboard'); // 改成你站內登入後首頁
        // }
        $user = $request->user();

        // dd($this->user->force_password_reset);
        return view('auth.force-reset-password', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // if (! $user->force_password_reset) {
        //     return redirect()->route('dashboard');
        // }

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ],
        ], [
            'current_password.required' => '請輸入原密碼',
            'password.required' => '請輸入新密碼',
            'password.min' => '新密碼至少 8 碼',
            'password.letters' => '新密碼需包含英文字母',
            'password.numbers' => '新密碼需包含數字',
            'password.symbols' => '新密碼需包含符號',
            'password.confirmed' => '兩次輸入的新密碼不一致',
        ]);

        // 驗證「臨時密碼」是否正確（就是目前的 password）
        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => '原密碼不正確'])->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'force_password_reset' => false,
            'temp_password_issued_at' => null,
            // 可選：換密碼後讓 remember_token 失效（踢掉其他裝置）
            'remember_token' => \Illuminate\Support\Str::random(60),
        ]);

        return redirect()->route('login')->with('status', '密碼已更新，請用新密碼繼續使用。');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        // // 避免刪到自己（強烈建議）
        // if (auth()->id() === $user->id) {
        //     return back()->with('status', '不能刪除自己');
        // }

        DB::transaction(function () use ($user) {
            // 先刪關聯（user_scope / user_scopes）
            $user->userScopes()->delete();

            // 再刪 user（如果你有 SoftDeletes，這裡會是 soft delete）
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('status', '已刪除使用者與其權限範圍');
    }
}
