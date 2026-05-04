<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedsUnkPhoto extends Model
{
    use HasFactory;

    protected $table = 'unkphoto';
    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $guarded = [];
}
