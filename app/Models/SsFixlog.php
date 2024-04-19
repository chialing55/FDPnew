<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsFixlog extends Model
{
    use HasFactory;
    protected $table = "fixlog";
    protected $connection = 'mysql5';
}
