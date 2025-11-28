<?php
//研究成果小主題
namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchOutputSection extends Model
{
    use HasFactory;

    protected $table = 'research_output_sections';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'research_output_id',
        'order_no',
        'title_zh_tw',
        'title_en',
        'content_zh_tw',
        'content_en',
        'chart_json',
        'image_path',
    ];

    /** 依語系回傳標題 */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    /** 依語系回傳內容 */
    public function getContentAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->content_en
            : $this->content_zh_tw;
    }

    /** 所屬的研究成果 */
    public function output()
    {
        return $this->belongsTo(ResearchOutput::class, 'research_output_id');
    }
}
