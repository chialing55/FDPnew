<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class ContentBlockItem extends Model
{
    protected $connection = 'mysql_web';

    protected $attributes = [
        'sort_order' => 1,
    ];

    protected $fillable = [
        'type',
        'body_zh_tw',
        'body_en',
        'component',
        'params',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'params' => 'array',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function contentBlock()
    {
        return $this->belongsTo(ContentBlock::class);
    }

    public function getBodyAttribute(): ?string
    {
        return app()->getLocale() === 'en' ? $this->body_en : $this->body_zh_tw;
    }
}
