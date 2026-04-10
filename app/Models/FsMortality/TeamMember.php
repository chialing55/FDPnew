<?php

namespace App\Models\FsMortality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $table = 'team_members';
    protected $connection = 'fs_mortality';

    protected $fillable = [
        'team_id',
        'person_id',
        'role',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
