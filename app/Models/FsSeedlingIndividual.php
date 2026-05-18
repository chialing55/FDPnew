<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsSeedlingIndividual extends Model
{
    use HasFactory;

    protected $table = 'seedling_individuals';
    protected $connection = 'mysql3';
}
