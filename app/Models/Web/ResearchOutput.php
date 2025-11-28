<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchOutput extends Model
{
    use HasFactory;

    protected $table = 'research_outputs';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'slug',
        'title_zh_tw',
        'title_en',
        'summary_zh_tw',
        'summary_en',
        'cover_image',
        'year',
        'status',
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

    /** 小主題區塊（sections） */
    public function sections()
    {
        return $this->hasMany(ResearchOutputSection::class, 'research_output_id')
            ->orderBy('order_no');
    }

    /** 只取發布中的成果 */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function siteTags()
    {
        return $this->hasMany(EntityTag::class, 'entity_id')
            ->where('entity_type', 'research_output')
            ->where('tag_type', 'site');
    }
}
