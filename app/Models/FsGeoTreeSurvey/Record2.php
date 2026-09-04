<?php

namespace App\Models\FsGeoTreeSurvey;

class Record2 extends FsGeoTreeSurveyModel
{
    protected $table = 'record2';

    protected $primaryKey = 'stemid';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
