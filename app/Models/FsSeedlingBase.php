<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingData extends Model
{
    use HasFactory;
    protected $table = "seedling";
    protected $connection = 'mysql3';
}
