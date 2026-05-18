<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingRecord extends Model
{
    use HasFactory;

    protected $table = 'seedling_records';
    protected $connection = 'mysql3';
}
