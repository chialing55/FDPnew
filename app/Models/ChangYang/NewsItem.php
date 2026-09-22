<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_news';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['category_year' => 'integer', 'category_month' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('category_year')
            ->orderByDesc('category_month')->orderByDesc('created_at')->orderByDesc('id');
    }
}
