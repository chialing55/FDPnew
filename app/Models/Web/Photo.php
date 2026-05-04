<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $table = 'photo';
    protected $connection = 'mysql_web';
    public $timestamps = false;
    protected $guarded = [];
}
