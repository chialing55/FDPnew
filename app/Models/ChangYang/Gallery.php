<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gallery extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_galleries';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    /** The only child image needed by the management-list cover preview. */
    public function previewItem(): HasOne
    {
        return $this->hasOne(GalleryItem::class)->ofMany(
            ['is_cover' => 'max', 'sort_order' => 'min', 'id' => 'min'],
            fn ($query) => $query->where('is_active', true),
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
