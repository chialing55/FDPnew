<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Census extends Model
{
    use HasFactory;

    protected $table = 'censuses';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'census',
        'survey_year',
        'has_dbh',
        'dbh_census',
        'data_batch',
    ];

    protected $casts = [
        'has_dbh' => 'boolean',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'census', 'census');
    }

    public function censusRecords()
    {
        return $this->hasMany(CensusRecord::class, 'census', 'census');
    }
}
