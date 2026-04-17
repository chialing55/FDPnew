<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportStage extends Model
{
    use HasFactory;

    protected $table = 'import_stage';
    protected $connection = 'fs_mortality';

    public $timestamps = false;

    protected $fillable = [
        'map',
        'q20',
        'q10',
        'qx',
        'qy',
        'subqx',
        'subqy',
        'stemid',
        'sp',
        'x',
        'y',
        'dbh1',
        'dbh2',
        'status',
        'mode',
        'living_length',
        'branches',
        'illumination',
        'leaning',
        'liana',
        'fungi',
        'wounded_stem',
        'deformity',
        'rotten',
        'leaves',
        'leaf_damage',
        'comments',
        'comments_json',
        'stem_corrections_json',
        'date',
        'people',
        'team_id',
        'created_at',
    ];

    protected $casts = [
        'comments_json' => 'array',
        'stem_corrections_json' => 'array',
        'date' => 'date',
        'created_at' => 'datetime',
        'team_id' => 'integer',
    ];
}
