<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Site;
use Illuminate\Validation\Rule;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $sites = Site::where('is_active', 1)
            ->where('code', '!=', 'all')
            ->orderBy('id')
            ->get();


        return view('auth.register', [
            'sites' => $sites,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'account' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:' . User::class . ',account'],
            'email'   => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email'],

            'role'   => ['required', 'in:資料管理員,計畫主持人,研究助理'],
            'site_id' => [
                'required',
                'integer',
                Rule::exists('sites', 'id')->where(fn($q) => $q->where('is_active', 1)->where('code', '!=', 'all')),
            ],


            // 如果表單沒有 unit，這行可以先拿掉
            'unit'    => ['nullable', 'string', 'max:255'],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)->letters()->numbers()->symbols(),
            ],
        ], [
            'role.required'   => '請選擇職稱。',
            'role.in'         => '職稱選項不正確。',
            'site_id.required' => '請選擇研究樣區。',
            'site_id.exists'   => '所選樣區不存在。',

            'password.required'  => '請輸入密碼。',
            'password.confirmed' => '兩次輸入的密碼不一致。',
            'password.min'       => '密碼至少需 8 個字元。',
            'password.letters'   => '密碼必須包含英文字母。',
            'password.numbers'   => '密碼必須包含數字。',
            'password.symbols'   => '密碼必須包含符號（如 !@#$%^&* ）。',
        ]);


        $role = match ($validated['role']) {
            '資料管理員' => 'admin',
            '計畫主持人' => 'pi',
            '研究助理' => 'ra',
            default => 'ra',
        };

        $user = User::create([
            'name'     => $validated['name'],
            'account'  => $validated['account'],
            'email'    => $validated['email'] ?? null,
            'unit'     => $validated['unit'] ?? null,
            'site_id'  => $validated['site_id'],
            'role'     => $role,
            'password' => Hash::make($validated['password']),
            'status'   => 'pending',
        ]);

        return redirect()->route('login')
            ->with('status', '註冊成功，請等待管理員審核。');
    }
}
