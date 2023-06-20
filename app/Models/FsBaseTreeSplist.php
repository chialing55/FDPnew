<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsBaseTreeSplist extends Model
{
    use HasFactory;
    protected $table = "tree_splist";
    protected $connection = 'mysql4';
}
