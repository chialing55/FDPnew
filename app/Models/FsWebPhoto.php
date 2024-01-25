<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsWebPhoto extends Model
{
    use HasFactory;
    protected $table = "photo";
    protected $connection = 'mysql6';
}
