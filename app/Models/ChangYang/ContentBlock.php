<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBlock extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'changyang_content_blocks';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['settings' => 'array', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'section_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlockImage::class, 'content_block_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
