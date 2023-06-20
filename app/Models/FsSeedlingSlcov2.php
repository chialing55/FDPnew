<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingSlcov2 extends Model
{
    use HasFactory;
    protected $table = "slcov2";
    protected $connection = 'mysql3';
}
