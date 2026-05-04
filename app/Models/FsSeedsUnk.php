<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedsUnk extends Model
{
    use HasFactory;

    protected $table = 'unknown';
    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $guarded = [];
}
