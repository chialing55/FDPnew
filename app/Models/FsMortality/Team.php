<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'census',
    ];

    public function censusRecords()
    {
        return $this->hasMany(CensusRecord::class);
    }

    public function censusItem()
    {
        return $this->belongsTo(Census::class, 'census', 'census');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }
}
