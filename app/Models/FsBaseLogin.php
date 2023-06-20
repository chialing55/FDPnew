<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FsBaseLogin extends Model
{
    use HasFactory;
    protected $table = "login";
    protected $connection = 'mysql4';
}
