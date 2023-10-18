<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsPlotCluster extends Model
{
    use HasFactory;
    protected $table = "cluster";
    protected $connection = 'mysql5';
}
