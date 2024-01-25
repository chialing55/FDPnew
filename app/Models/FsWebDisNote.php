<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsWebDisNote extends Model
{
    use HasFactory;
    protected $table = "dis_note";
    protected $connection = 'mysql6';
}
