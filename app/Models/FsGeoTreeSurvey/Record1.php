<?php

namespace App\Models\FsGeoTreeSurvey;

class Record1 extends FsGeoTreeSurveyModel
{
    protected $table = 'record1';

    protected $primaryKey = 'stemid';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
