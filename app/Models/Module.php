<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 這個 module 有哪些 user_scopes
    public function userScopes()
    {
        return $this->hasMany(UserScope::class, 'module_id');
    }

    // （可選）透過 user_scopes 找到有哪些 users 擁有此 module 權限
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_scopes', 'module_id', 'user_id')
            ->withPivot(['site_id', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }
}
