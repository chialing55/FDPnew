<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsTreeRecord1Alter extends Model
{
    use HasFactory;
    protected $table = "record1_alter";
    protected $connection = 'mysql';
}
