<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FsTreeCensus4 extends Model
{
    use HasFactory;

    protected $table = "census4";
    protected $connection = 'mysql';
}
