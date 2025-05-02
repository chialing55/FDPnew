<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FsTreeCensus2 extends Model
{
    use HasFactory;

    protected $table = "census2";
    protected $connection = 'mysql1';
}
