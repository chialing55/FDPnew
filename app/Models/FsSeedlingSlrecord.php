<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingSlrecord extends Model
{
    use HasFactory;
    protected $table = "slrecord";
    protected $connection = 'mysql3';
}
