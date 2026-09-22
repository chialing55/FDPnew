<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_gallery_items';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'is_cover' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->alt_text = $item->title ?? '';
            if (! $item->is_active) $item->is_cover = false;
        });

        static::saved(function (self $item): void {
            if ($item->is_cover) {
                static::query()->where('gallery_id', $item->gallery_id)->where('id', '!=', $item->id)->update(['is_cover' => false]);
            }
        });
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
