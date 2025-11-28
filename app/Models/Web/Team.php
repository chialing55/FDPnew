<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'team_type',          // academic / government / ngo / other
        'name_zh_tw',
        'name_en',
        'short_name_zh_tw',
        'short_name_en',
        'institution_zh_tw',
        'institution_en',
        'department_zh_tw',
        'department_en',
        'pi_name_zh_tw',
        'pi_name_en',
        'description_zh_tw',
        'description_en',
        'website_url',
        'logo_path',
        'is_active',
    ];

    /** 依語系回傳團隊名稱 */
    public function getNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->name_en
            : $this->name_zh_tw;
    }

    /** 依語系回傳簡稱 */
    public function getShortNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->short_name_en
            : $this->short_name_zh_tw;
    }

    public function getInstitutionAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->institution_en
            : $this->institution_zh_tw;
    }

    public function getDepartmentAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->department_en
            : $this->department_zh_tw;
    }

    public function getPiNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->pi_name_en
            : $this->pi_name_zh_tw;
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->description_en
            : $this->description_zh_tw;
    }

    /** 參與的樣區（透過 site_team pivot） */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_team', 'team_id', 'site_id')
            ->withTimestamps();
    }
}
