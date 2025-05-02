<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeProgress extends Model
{
    use HasFactory;
    protected $table = "progress";
    protected $connection = 'mysql1';
}
