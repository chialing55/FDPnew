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
        'view_name', 'description', 'hero_image',
        'nav_group', 'nav_order',
    ];

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    public function blocks()
    {
        return $this->hasMany(ContentBlock::class, 'owner_id')
            ->where('owner_type', 'pages')
            ->orderBy('sort_order');
    }

}

