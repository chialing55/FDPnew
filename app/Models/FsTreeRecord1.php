<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeRecord1 extends Model
{
    use HasFactory;
    protected $table = "record1";
    protected $connection = 'mysql1';
}
