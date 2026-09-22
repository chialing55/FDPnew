<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_pages';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['hero_settings' => 'array', 'show_in_navigation' => 'boolean', 'navigation_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
