<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory;

    protected $table = 'content_blocks';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'block_type',
        'title_zh_tw',
        'title_en',
        'body_zh_tw',
        'body_en',
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

    // 以下三個看需要用哪一個 owner，就挑著用

    public function page()
    {
        return $this->belongsTo(Page::class, 'owner_id')
            ->where('owner_type', 'page');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'owner_id')
            ->where('owner_type', 'site');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'owner_id')
            ->where('owner_type', 'topic');
    }
}
