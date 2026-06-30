<?php

namespace App\Models\FsGeoTreeSurvey;

use Illuminate\Database\Eloquent\Model;

abstract class FsGeoTreeSurveyModel extends Model
{
    protected $connection = 'fs_geo_tree_survey';

    protected $guarded = [];
}
