<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'slug',
        'title_zh_tw',
        'title_en',
        'summary_zh_tw',
        'summary_en',
        'content_zh_tw',
        'content_en',
        'cover_image',
        'publish_date',
        'external_url',
        'is_featured',
        'is_public',
    ];

    /** 依語系回傳標題 */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    /** 依語系回傳摘要 */
    public function getSummaryAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->summary_en
            : $this->summary_zh_tw;
    }

    /** 依語系回傳內容 */
    public function getContentAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->content_en
            : $this->content_zh_tw;
    }

    /** scope：只抓前台要顯示的 */
    public function scopePublic($query)
    {
        return $query->where('is_public', 1);
    }

    /** scope：照發布日期倒序 */
    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('publish_date');
    }
}
