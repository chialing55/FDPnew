<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentOption extends Model
{
    use HasFactory;

    protected $table = 'comment_options';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'code',
        'comment_zh',
        'comment_en',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function censusRecordComments()
    {
        return $this->hasMany(CensusRecordComment::class, 'comment_option_id');
    }
}
