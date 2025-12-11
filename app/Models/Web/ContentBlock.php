<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory;

    protected $table = 'content_blocks';
    protected $connection = 'mysql_web';
    protected $casts = [
        'attachments' => 'array',
        'params' => 'array',
    ];
    protected $fillable = [
        'page_id',
        'block_type',
        'title_zh_tw',
        'title_en',
        'body_zh_tw',
        'body_en',
        'attachments',
        'view',
        'params',
        'sort_order',
        'is_public',
    ];

    /** 依語系回傳標題 */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    /** 依語系回傳內容 */
    public function getBodyAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->body_en
            : $this->body_zh_tw;
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

}
