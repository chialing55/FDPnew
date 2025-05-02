<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeFixlog extends Model
{
    use HasFactory;
    protected $table = "fixlog";
    protected $connection = 'mysql1';
}
