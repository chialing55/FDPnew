<?php

namespace App\Models\PlantCatalog;

use Illuminate\Database\Eloquent\Model;

abstract class PlantCatalogModel extends Model
{
    protected $connection = 'plant_catalog';

    protected $guarded = [];
}
