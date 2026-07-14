<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

    use HasFactory;
    protected $table = "pages";
    protected $connection = 'mysql_web';

    protected $fillable = [
        'slug',
        'title_zh_tw', 'title_en',
        'description', 'hero_image',
        'nav_group', 'nav_order',
    ];


    public function getTitleAttribute()
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    public function contentBlocks()
    {
        return $this->morphMany(ContentBlock::class, 'owner');
    }

    public function site()
    {
        return $this->hasOne(Site::class);
    }

    public function subject()
    {
        return $this->hasOne(Subject::class);
    }
}

