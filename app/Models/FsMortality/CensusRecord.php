<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CensusRecord extends Model
{
    use HasFactory;

    protected $table = 'census_records';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'stemid',
        'census',
        'date',
        'dbh',
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
        'team_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'dbh' => 'decimal:2',
        'living_length' => 'decimal:2',
        'fungi' => 'boolean',
        'leaf_damage' => 'boolean',
    ];

    public function treeIndividual()
    {
        return $this->belongsTo(TreeIndividual::class, 'stemid', 'stemid');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function censusItem()
    {
        return $this->belongsTo(Census::class, 'census', 'census');
    }

    public function comments()
    {
        return $this->hasMany(CensusRecordComment::class);
    }

    public function stemCorrections()
    {
        return $this->hasMany(StemCorrection::class);
    }
}
