<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsComplete extends Model
{
    use HasFactory;
    protected $table = "complete";
    protected $connection = 'mysql5';
}
