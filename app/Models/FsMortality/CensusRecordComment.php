<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CensusRecordComment extends Model
{
    use HasFactory;

    protected $table = 'census_record_comments';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'census_record_id',
        'comment_option_id',
        'comment_other',
        'sort_order',
    ];

    public function censusRecord()
    {
        return $this->belongsTo(CensusRecord::class);
    }

    public function commentOption()
    {
        return $this->belongsTo(CommentOption::class, 'comment_option_id');
    }
}
