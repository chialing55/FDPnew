<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Web\Project;
use App\Models\Web\Site;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectSite extends Pivot
{
    protected $table = 'project_site';

    protected $fillable = [
        'project_id',
        'site_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
