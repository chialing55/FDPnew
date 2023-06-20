<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingCov extends Model
{
    use HasFactory;
    protected $table = "seedling_cov";
    protected $connection = 'mysql3';
}
