<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedsSplist extends Model
{
    use HasFactory;
    protected $table = "splist";
    protected $connection = 'mysql2';
}
