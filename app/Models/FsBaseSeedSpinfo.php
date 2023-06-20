<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsBaseSeedSpinfo extends Model
{
    use HasFactory;
    protected $table = "seed_spinfo";
    protected $connection = 'mysql4';
}
