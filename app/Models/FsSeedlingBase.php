<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingBase extends Model
{
    use HasFactory;
    protected $table = "base";
    protected $connection = 'mysql3';
}
