<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserScope extends Model
{
    protected $table = 'user_scopes';

    protected $fillable = [
        'user_id',
        'site_id',
        'module_id',
        'approved_at',
        'approved_by',
        // 之後若加 is_enabled 再補
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    // （可選）核准者：同樣是 users 表
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
