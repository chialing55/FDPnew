<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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
        'email',
        'password',
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
        'email_verified_at' => 'datetime',
    ];

    /**
     * Filament Panel access
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // 目前先規定：只有 super-admin 可以進 CMS
        return $this->hasRole('super-admin');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function userScopes()
    {
        return $this->hasMany(UserScope::class);
    }


    /**
     * 注意：這是「方便查」用的多對多，因為 pivot 仍有 module_id。
     * 真正權限建議仍以 userScopes 或 canScope() 為主。
     */
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
        // 1) 轉成 id（支援傳 code 或 id）
        $siteId = is_numeric($site) ? (int) $site : $this->siteIdByCode((string) $site);
        $moduleId = is_numeric($module) ? (int) $module : $this->moduleIdByCode((string) $module);

        if (!$siteId || !$moduleId) {
            return false; // code 找不到就視為沒權限
        }

        // 2) all 的 id（若你尚未建立 all，會是 null）
        $allSiteId = $this->siteIdByCode('all');
        $allModuleId = $this->moduleIdByCode('all');

        // 3) 用 Eloquent 走 UserScope（已是 Model，不用 DB::table）
        $q = UserScope::query()
            ->where('user_id', $this->id);

        if ($requireApproved) {
            $q->whereNotNull('approved_at');
        }

        // 若 all 不存在，就只做精確比對
        if (!$allSiteId || !$allModuleId) {
            $q->where('site_id', $siteId)
                ->where('module_id', $moduleId);

            return $q->exists();
        }

        // 含 all wildcard
        $q->whereIn('site_id', [$siteId, $allSiteId])
            ->whereIn('module_id', [$moduleId, $allModuleId]);

        return $q->exists();
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
}
