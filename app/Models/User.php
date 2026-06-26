<?php

namespace App\Models;

use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles; // ← Spatie 權限

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'account',
        'email',
        'password',
        'status',
        'role',
        'is_admin',
        'unit',
        'site_id',
        'approved_at',
        'approved_by',
        'can_access_filament',
        'force_password_reset',
        'temp_password_issued_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_admin' => 'boolean',
        'can_access_filament' => 'boolean',
        'force_password_reset' => 'boolean',
    ];

    /**
     * Filament Panel access
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessFilament();
    }

    public function canAccessFilament(): bool
    {
        return $this->hasRole('super-admin')
            || (bool) ($this->can_access_filament ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function userScopes()
    {
        return $this->hasMany(UserScope::class, 'user_id');
    }

    /**
     * 注意：這是「方便查」用的多對多，因為 pivot 仍有 module_id。
     * 真正權限建議仍以 userScopes 或 canScope() 為主。
     */

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'user_scopes', 'user_id', 'site_id')
            ->withPivot(['module_id', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    /**
     * 注意：這是「方便查」用的多對多，因為 pivot 仍有 site_id。
     * 真正權限建議仍以 userScopes 或 canScope() 為主。
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_scopes', 'user_id', 'module_id')
            ->withPivot(['site_id', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Permission (site + module)
    |--------------------------------------------------------------------------
    */

    /**
     * 檢查使用者是否擁有某個 site + module 的權限（含 all wildcard）
     *
     * @param string|int $site   sites.code（例：fushan / shoushan / all）或 site_id
     * @param string|int $module modules.code（例：tree / seed / seedling / plot / all）或 module_id
     * @param bool $requireApproved 是否要求 user_scopes.approved_at 不為 NULL（建議 true）
     */
    public function canScope(string|int $site, string|int $module, bool $requireApproved = true): bool
    {
        $allSiteId   = $this->siteIdByCode('all');
        $allModuleId = $this->moduleIdByCode('all');

        // 1) 轉成 id（支援 code 或 id；也允許傳 'all'）
        $siteId = is_numeric($site) ? (int) $site : $this->siteIdByCode((string) $site);
        $moduleId = is_numeric($module) ? (int) $module : $this->moduleIdByCode((string) $module);

        if (!$siteId || !$moduleId) {
            return false;
        }

        $q = UserScope::query()
            ->where('user_id', $this->id)
            ->where('is_enabled', 1);

        if ($requireApproved) {
            $q->whereNotNull('approved_at');
        }

        // 2) 如果 DB 裡沒有 all，就只能精確比對（你原本的邏輯）
        if (!$allSiteId || !$allModuleId) {
            return $q->where('site_id', $siteId)
                ->where('module_id', $moduleId)
                ->exists();
        }

        // 3) 有 all：wildcard 規則
        // 允許：
        // - 精確 (siteId, moduleId)
        // - 全站 (allSiteId, moduleId)
        // - 全模組 (siteId, allModuleId)
        // - 全部 (allSiteId, allModuleId)
        return $q->where(function ($qq) use ($siteId, $moduleId, $allSiteId, $allModuleId) {
            $qq->where(function ($q1) use ($siteId, $moduleId) {
                $q1->where('site_id', $siteId)->where('module_id', $moduleId);
            })
                ->orWhere(function ($q2) use ($allSiteId, $moduleId) {
                    $q2->where('site_id', $allSiteId)->where('module_id', $moduleId);
                })
                ->orWhere(function ($q3) use ($siteId, $allModuleId) {
                    $q3->where('site_id', $siteId)->where('module_id', $allModuleId);
                })
                ->orWhere(function ($q4) use ($allSiteId, $allModuleId) {
                    $q4->where('site_id', $allSiteId)->where('module_id', $allModuleId);
                });
        })
            ->exists();
    }


    /**
     * sites.code -> sites.id（永久快取）
     */
    protected function siteIdByCode(string $code): ?int
    {
        $code = strtolower(trim($code));

        return Cache::rememberForever("sites.id_by_code:{$code}", function () use ($code) {
            return Site::query()->where('code', $code)->value('id');
        });
    }

    /**
     * modules.code -> modules.id（永久快取）
     */
    protected function moduleIdByCode(string $code): ?int
    {
        $code = strtolower(trim($code));

        return Cache::rememberForever("modules.id_by_code:{$code}", function () use ($code) {
            return Module::query()->where('code', $code)->value('id');
        });
    }

    /**
     * 如果你之後更新 sites/modules 的 code 或新增 all，記得清 cache：
     * php artisan cache:clear
     */


    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => '資料管理員',
            'pi'    => '計畫主持人',
            'ra'    => '研究助理',
            default => '未知',
        };
    }
}
