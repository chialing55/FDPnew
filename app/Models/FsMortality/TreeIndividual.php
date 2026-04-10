<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreeIndividual extends Model
{
    use HasFactory;

    protected $table = 'tree_individuals';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'stemid',
        'spcode',
        'qx',
        'qy',
        'subqx',
        'subqy',
        'qudx',
        'qudy',
        'is_active',
        'note',
    ];

    public function censusRecords()
    {
        return $this->hasMany(CensusRecord::class, 'stemid', 'stemid');
    }

    public function stemCorrections()
    {
        return $this->hasMany(StemCorrection::class, 'stemid', 'stemid');
    }
}
