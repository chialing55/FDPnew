<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record1 extends Model
{
    use HasFactory;

    protected $table = 'record1';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'census',
        'map',
        'qx',
        'qy',
        'subqx',
        'subqy',
        'stemid',
        'csp',
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
        'comments_json',
        'stem_corrections_json',
        'date',
        'team_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'dbh1' => 'decimal:2',
        'dbh2' => 'decimal:2',
        'living_length' => 'decimal:2',
        'fungi' => 'integer',
        'leaf_damage' => 'integer',
        'comments_json' => 'array',
        'stem_corrections_json' => 'array',
    ];

    public function censusItem()
    {
        return $this->belongsTo(Census::class, 'census', 'census');
    }

    public function treeIndividual()
    {
        return $this->belongsTo(TreeIndividual::class, 'stemid', 'stemid');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
