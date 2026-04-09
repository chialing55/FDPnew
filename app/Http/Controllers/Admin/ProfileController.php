<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $sites = Site::where('is_active', 1)->orderBy('id')->get();

        return view('admin.users.profile', compact('user', 'sites'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $canEditRole = $user->role !== 'admin';

        $validated = $request->validate([
            'unit' => ['nullable', 'string', 'max:255'],
            'role' => $canEditRole
                ? ['required', Rule::in(['pi', 'ra'])]
                : ['nullable'],
            'site_id' => [
                'nullable',
                'integer',
                Rule::exists('sites', 'id')->where(fn($q) => $q->where('is_active', 1)),
            ],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ],
        ], [
            'role.required' => '請選擇角色',
            'role.in' => '角色只能選擇計畫主持人或研究助理',
            'current_password.required_with' => '若要修改密碼，請先輸入目前密碼',
            'password.min' => '新密碼至少 8 碼',
            'password.letters' => '新密碼需包含英文字母',
            'password.numbers' => '新密碼需包含數字',
            'password.symbols' => '新密碼需包含符號',
            'password.confirmed' => '兩次輸入的新密碼不一致',
        ]);

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => '目前密碼不正確'])->withInput();
            }

            $user->password = Hash::make($validated['password']);
            $user->force_password_reset = false;
            $user->temp_password_issued_at = null;
        }

        $user->unit = $validated['unit'] ?? null;
        $user->site_id = $validated['site_id'] ?? null;

        if ($canEditRole) {
            $user->role = $validated['role'];
        }

        $user->save();

        return redirect()->route('admin.profile.edit')->with('status', '個人資訊已更新');
    }
}
