<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsSplist extends Model
{
    use HasFactory;
    protected $table = "splist";
    protected $connection = 'mysql5';
}
