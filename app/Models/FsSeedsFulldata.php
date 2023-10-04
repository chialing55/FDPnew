<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedsFulldata extends Model
{
    use HasFactory;
    protected $table = "fulldata";
    protected $connection = 'mysql2';
}
