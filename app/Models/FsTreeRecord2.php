<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeRecord2 extends Model
{
    use HasFactory;
    protected $table = "record2";
    protected $connection = 'mysql1';
}
