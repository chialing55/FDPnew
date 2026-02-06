<?php
// app/Http/Controllers/Admin/UserApprovalController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserApprovalController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()?->is_admin) {
            abort(403, 'Only admin.');
        }
    }

    public function index()
    {
        $this->ensureAdmin(); // ← 關鍵
        $users = User::where('status', 'pending')->orderBy('created_at')->get();
        return view('admin.users.pending', compact('users'));
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
        return back()->with('status', "已通過：{$user->email}");
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
        return back()->with('status', "已拒絕：{$user->email}");
    }
}
