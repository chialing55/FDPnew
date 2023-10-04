<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsBaseSeedsSpinfo extends Model
{
    use HasFactory;
    protected $table = "seeds_spinfo";
    protected $connection = 'mysql4';
}
