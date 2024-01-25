<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ss10mBase2015 extends Model
{
    use HasFactory;
    protected $table = "10m_base_2015";
    protected $connection = 'mysql5';
}
