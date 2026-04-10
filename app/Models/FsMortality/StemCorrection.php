<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StemCorrection extends Model
{
    use HasFactory;

    protected $table = 'stem_corrections';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'stemid',
        'census_record_id',
        'correction_type',
        'field_name',
        'old_value',
        'new_value',
        'description',
        'status',
        'applied_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function treeIndividual()
    {
        return $this->belongsTo(TreeIndividual::class, 'stemid', 'stemid');
    }

    public function censusRecord()
    {
        return $this->belongsTo(CensusRecord::class);
    }
}
