<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingSlcov1 extends Model
{
    use HasFactory;
    protected $table = "slcov1";
    protected $connection = 'mysql3';
}
