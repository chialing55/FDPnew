<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSection extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'changyang_page_sections';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['settings' => 'array', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class, 'section_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
