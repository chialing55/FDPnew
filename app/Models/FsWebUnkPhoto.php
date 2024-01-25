<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsWebUnkPhoto extends Model
{
    use HasFactory;
    protected $table = "unkphoto";
    protected $connection = 'mysql6';
}
