<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeCensuses extends Model
{
    use HasFactory;

    protected $table = "censuses";
    protected $connection = 'mysql1';
}
