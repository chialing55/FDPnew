<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntityTag extends Model
{
    use HasFactory;

    protected $table = 'entity_tags';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'entity_type',   // page(research_output) / research_project / publication
        'entity_id',
        'site_id',      // site / topic
        'subject_id',
    ];

    /** 所屬樣區（tag_type = site 時才有意義） */
    public function site()
    {
        return $this->belongsTo(Site::class, 'tag_id')
            ->where('tag_type', 'site');
    }

    /** 所屬主題（tag_type = topic 時才有意義） */
    public function topic()
    {
        return $this->belongsTo(Subject::class, 'tag_id')
            ->where('tag_type', 'topic');
    }

    /** scope：限定某種 entity */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /** scope：限定某種 tag 類型（site / topic） */
    public function scopeForTagType($query, string $tagType)
    {
        return $query->where('tag_type', $tagType);
    }
}
