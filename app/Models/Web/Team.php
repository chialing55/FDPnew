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
        return $this->belongsToMany(Site::class, 'site_team')
            ->withPivot('role', 'sort_order');
    }

    public function siteTeams()
    {
        return $this->hasMany(SiteTeam::class);
    }

    /** 顯示名稱：機構 / 部門 / PI 名（後台與前台都可以統一使用） */
    public function getDisplayNameAttribute(): string
    {
        return collect([
            $this->institution_zh_tw,
            $this->department_zh_tw,
            $this->pi_name_zh_tw,
        ])->filter(fn (?string $value): bool => filled($value))
            ->implode(' / ');
    }

}
