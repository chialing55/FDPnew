<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsWebUnk extends Model
{
    use HasFactory;
    protected $table = "unknown";
    protected $connection = 'mysql6';
}
