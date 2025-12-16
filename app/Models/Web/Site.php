<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'page_id',
        'name_zh_tw',
        'name_en',
        'short_name_zh_tw',
        'short_name_en',
        'description_zh_tw',
        'description_en',
        'is_active',
        'latitude',
        'longitude',
        'elevation_m',
        // 有其它欄位再自己加進來
    ];

    /** 依語系回傳名稱 */
    public function getNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->name_en
            : $this->name_zh_tw;
    }

    public function getShortNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->short_name_en
            : $this->short_name_zh_tw;
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->description_en
            : $this->description_zh_tw;
    }

    /** 樣區相關的內容區塊 */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function siteTeams()
    {
        return $this->hasMany(SiteTeam::class);
    }    
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'site_team')
            ->withPivot('role', 'sort_order');
    }

    public function researchOutputs()
    {
        return $this->belongsToMany(
            ResearchOutput::class,
            'research_output_site'
        );
    }    
}
