<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchOutput extends Model
{
    use HasFactory;

    protected $table = 'research_outputs';
    protected $connection = 'mysql_web';
    protected $casts = [
        'params' => 'array',
        'is_public' => 'boolean',
    ];

    protected $fillable = [
        'slug',
        'title_zh_tw',
        'title_en',
        'body_zh_tw',
        'body_en',
        'view',
        'params',
        'hero_image',
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
    public function getBodyAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->body_en
            : $this->body_zh_tw;
    }


    public function contentBlocks()
    {
        return $this->morphMany(ContentBlock::class, 'owner');
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

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'research_output_subject'
        )->withTimestamps();;
    }

    public function sites()
    {
        return $this->belongsToMany(
            Site::class,
            'research_output_site'
        )->withTimestamps();;
    }
}
