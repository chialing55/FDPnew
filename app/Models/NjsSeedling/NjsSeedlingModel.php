<?php

namespace App\Models\NjsSeedling;

use Illuminate\Database\Eloquent\Model;

abstract class NjsSeedlingModel extends Model
{
    protected $connection = 'njs_seedling';

    protected $guarded = [];
}
