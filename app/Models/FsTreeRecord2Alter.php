<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeRecord2Alter extends Model
{
    use HasFactory;
    protected $table = "record2_alter";
    protected $connection = 'mysql';
}
