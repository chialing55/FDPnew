<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeCensus3 extends Model
{
    use HasFactory;
    protected $table = "census3";
    protected $connection = 'mysql';
}
