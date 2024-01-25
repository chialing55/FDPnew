<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsBaseSpinfo extends Model
{
    use HasFactory;
    protected $table = "spinfo";
    protected $connection = 'mysql4';
}
