<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()?->is_admin) {
            abort(403, 'Only admin.');
        }
    }

    /**
     * 使用者列表
     */
    public function index()
    {
        $this->ensureAdmin(); // ← 關鍵
        $users = User::with([
            'site',
            'sites',
            'userScopes' => function ($q) {
                $q->where('is_enabled', 1)
                    ->with([
                        'site:id,name',
                        'module:id,name',
                    ]);
            },
        ])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();


        return view('admin.users.index', compact('users'));
    }

    /**
     * 編輯 / 分派權限
     */
    public function edit(User $user)
    {
        $this->ensureAdmin(); // ← 關鍵
        $sites = Site::where('is_active', 1)
            // ->where('code', '!=', 'all')
            ->orderBy('id')
            ->get();

        // module_id -> module_key 映射（有 modules 表最好）
        $moduleKeyById = [];

        $moduleKeyById = DB::table('modules')->pluck('code', 'id')->all(); // [id => key]


        $rows = DB::table('user_scopes')
            ->where('user_id', $user->id)
            ->where('is_enabled', 1)
            ->get(['site_id', 'module_id']);

        $enabledMap = [];
        foreach ($rows as $r) {
            $mid = (int)$r->module_id;
            $mKey = $moduleKeyById[$mid] ?? null;
            if (!$mKey) continue;

            $enabledMap[(int)$r->site_id][$mKey] = true;
        }

        return view('admin.users.edit', compact('user', 'sites', 'enabledMap'));
    }


    /**
     * 儲存分派（以 user_scopes 為準）
     *
     * 表單預期欄位：
     * - unit
     * - role
     * - site_id（主要樣區，可空）
     * - scopes[site_id][] = module_id （打勾的權限）
     */
    public function update(Request $request, User $user)
    {
        $this->ensureAdmin(); // ← 關鍵
        $validated = $request->validate([
            'unit' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'pi', 'ra'])],
            'site_id' => [
                'nullable',
                'integer',
                Rule::exists('sites', 'id')->where(fn($q) => $q->where('is_active', 1)),
            ],
            'can_access_filament' => ['nullable', 'boolean'],
            'scopes' => ['nullable', 'array'],
        ]);

        // module_key -> module_id

        $moduleIdByKey = DB::table('modules')->pluck('id', 'code')->all(); // [key => id]

        $siteIdsAllowed = Site::where('is_active', 1)->pluck('id')->map(fn($v) => (int)$v)->all();

        $scopes = $request->input('scopes', []);
        if (!is_array($scopes)) $scopes = [];
        $canAccessFilament = $request->boolean('can_access_filament');

        $wantedPairs = [];
        foreach ($scopes as $siteId => $moduleMap) {
            $siteId = (int)$siteId;
            if (!in_array($siteId, $siteIdsAllowed, true)) continue;
            if (!is_array($moduleMap)) continue;

            foreach ($moduleMap as $moduleKey => $val) {
                if (!$val) continue; // 沒勾
                $moduleId = $moduleIdByKey[$moduleKey] ?? null;
                if (!$moduleId) continue;

                $wantedPairs[] = ['site_id' => $siteId, 'module_id' => (int)$moduleId];
            }
        }

        DB::transaction(function () use ($user, $validated, $wantedPairs, $canAccessFilament) {
            $now = now();
            $adminId = Auth::id();

            $user->unit = $validated['unit'] ?? null;
            $user->role = $validated['role'];
            $user->is_admin = $validated['role'] === 'admin';
            $user->site_id = $validated['site_id'] ?? null;
            $user->can_access_filament = $canAccessFilament;
            $user->save();

            // 先全部關閉
            DB::table('user_scopes')
                ->where('user_id', $user->id)
                ->update(['is_enabled' => 0, 'updated_at' => $now]);

            // 再把勾選的打開（存在就 update，不存在就 insert）
            foreach ($wantedPairs as $p) {
                $exists = DB::table('user_scopes')
                    ->where('user_id', $user->id)
                    ->where('site_id', $p['site_id'])
                    ->where('module_id', $p['module_id'])
                    ->exists();

                if ($exists) {
                    DB::table('user_scopes')
                        ->where('user_id', $user->id)
                        ->where('site_id', $p['site_id'])
                        ->where('module_id', $p['module_id'])
                        ->update([
                            'is_enabled' => 1,
                            'approved_at' => $now,
                            'approved_by' => $adminId,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('user_scopes')->insert([
                        'user_id' => $user->id,
                        'site_id' => $p['site_id'],
                        'module_id' => $p['module_id'],
                        'is_enabled' => 1,
                        'approved_at' => $now,
                        'approved_by' => $adminId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        return redirect()->route('admin.users.edit', $user)->with('status', '已儲存使用者樣區與工作權限。');
    }

    /**
     * 重設使用者密碼（產生臨時密碼）
     */


    public function resetPassword(User $user)
    {
        $this->ensureAdmin();

        // 產生臨時密碼（可自行調整規則）
        $temp = Str::random(10);

        // 一次 update 完成：設定臨時密碼 + 強制換密碼旗標
        $user->update([
            'password' => Hash::make($temp),
            'force_password_reset' => true,
            'temp_password_issued_at' => now(),
            // 可選：讓舊的「記住我」失效（避免別的裝置還能用舊 session）
            // 'remember_token' => Str::random(60),
        ]);

        // 只顯示一次，避免留在 log 或 DB
        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', '已重設密碼（請立即提供臨時密碼給使用者）')
            ->with('temp_password', $temp);
    }

    /**
     * 刪除一般使用者及其工作權限。
     */
    public function destroy(User $user)
    {
        $this->ensureAdmin();

        if (Auth::id() === $user->id) {
            return back()->with('status', '不能刪除目前登入的帳號。');
        }

        if ($user->is_admin) {
            return back()->with('status', '不能刪除管理員帳號。');
        }

        DB::transaction(function () use ($user) {
            $user->userScopes()->delete();
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('status', '已刪除使用者與其權限範圍。');
    }


    public function approve(Request $request, User $user)
    {
        $this->ensureAdmin(); // ← 關鍵
        if ($user->status !== 'pending') abort(400, '此帳號不在待審中');
        $user->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);
        return back()->with('status', "已通過：{$user->account}");
    }

    public function reject(Request $request, User $user)
    {
        $this->ensureAdmin(); // ← 關鍵
        if ($user->status !== 'pending') abort(400, '此帳號不在待審中');
        $user->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => $request->user()->id,
        ]);
        return back()->with('status', "已拒絕：{$user->account}");
    }
}
