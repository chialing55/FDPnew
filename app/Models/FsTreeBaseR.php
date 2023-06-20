<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeBaseR extends Model
{
    use HasFactory;
    protected $table = "base_r";
    protected $connection = 'mysql';
}
