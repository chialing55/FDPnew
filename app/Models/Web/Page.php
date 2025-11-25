<?php

namespace App\Models\WEb;

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
        'content_zh_tw', 'content_en',
    ];

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    public function getContentAttribute()
    {
        return app()->getLocale() === 'en'
            ? $this->content_en
            : $this->content_zh_tw;
    }
}

