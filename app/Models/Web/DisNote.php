<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisNote extends Model
{
    use HasFactory;

    protected $table = 'dis_note';
    protected $connection = 'mysql_web';
    public $timestamps = false;
    protected $guarded = [];
}
