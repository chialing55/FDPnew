<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 這個 site 有哪些 user_scopes
    public function userScopes()
    {
        return $this->hasMany(UserScope::class, 'site_id');
    }

    // （可選）透過 user_scopes 找到有哪些 users 擁有此 site 權限
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_scopes', 'site_id', 'user_id')
            ->withPivot(['module_id', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }
}
