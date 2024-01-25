<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeCensus5 extends Model
{
    use HasFactory;

    protected $table = "census5";
    protected $connection = 'mysql';
}
