<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingStem extends Model
{
    use HasFactory;

    protected $table = 'seedling_stems';
    protected $connection = 'mysql3';
}
