<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsEntrycom extends Model
{
    use HasFactory;
    protected $table = "entrycom";
    protected $connection = 'mysql5';
}
