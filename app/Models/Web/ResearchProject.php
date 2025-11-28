<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchProject extends Model
{
    use HasFactory;

    protected $table = 'research_projects';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'slug',
        'code',             // 計畫編號（有的話）
        'title_zh_tw',
        'title_en',
        'summary_zh_tw',
        'summary_en',
        'pi_zh_tw',
        'pi_en',
        'start_date',
        'end_date',
        'funding_agency_zh_tw',
        'funding_agency_en',
        'website_url',           
        'is_active',
        'website_url',
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

    /** 依語系回傳說明 */
    public function getPiAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->pi_en
            : $this->pi_zh_tw;
    }

    /** scope：前台可見 */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }


    /** 此計畫對應的 entity_tags 記錄 */
    public function entityTags()
    {
        return $this->hasMany(EntityTag::class, 'entity_id')
            ->where('entity_type', 'research_project');
    }
}
