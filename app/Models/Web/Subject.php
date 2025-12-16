<?php
//研究成果
namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'page_id',
        'name_zh_tw',
        'name_en',
        'short_name_zh_tw',
        'short_name_en',
        'description_zh_tw',
        'description_en',
        'method_zh_tw',
        'method_en',
        'is_active',
        'sort_order',
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

    public function getMethodAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->method_en
            : $this->method_zh_tw;
    }
    /** 主題相關的內容區塊 */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function researchOutputs()
    {
        return $this->belongsToMany(
            ResearchOutput::class,
            'research_output_subject'
        );
    }
}
