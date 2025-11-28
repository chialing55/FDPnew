<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $table = 'publications';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'slug',
        'title_zh_tw',
        'title_en',
        'citation_zh_tw',   // 完整引用（中）
        'citation_en',      // 完整引用（英）
        'year',
        'journal_zh_tw',
        'journal_en',
        'volume',
        'issue',
        'pages',
        'doi',
        'url',
        'external_id',
        'is_open_access',

    ];

    /** 依語系回傳標題 */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    /** 依語系回傳引用文字 */
    public function getCitationAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->citation_en
            : $this->citation_zh_tw;
    }

    /** 依語系回傳期刊名稱 */
    public function getJournalAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->journal_en
            : $this->journal_zh_tw;
    }

    /** scope：依年份倒序 */
    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('year');
    }

    /** 此論文對應的 entity_tags 記錄 */
    public function entityTags()
    {
        return $this->hasMany(EntityTag::class, 'entity_id')
            ->where('entity_type', 'publication');
    }
}
