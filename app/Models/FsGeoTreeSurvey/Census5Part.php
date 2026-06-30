<?php

namespace App\Models\FsGeoTreeSurvey;

class Census5Part extends FsGeoTreeSurveyModel
{
    protected $table = 'census5_part';

    protected $primaryKey = 'stemid';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
