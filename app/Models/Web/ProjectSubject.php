<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectSubject extends Pivot
{
    protected $table = 'project_subject';

    protected $fillable = [
        'project_id',
        'subject_id',
        'other_zh_tw',
        'other_en',
    ];

    protected $casts = [
        'other_zh_tw' => 'string',
        'other_en' => 'string',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * 是否為 Other 主題
     */
    public function isOther(): bool
    {
        return optional($this->subject)->code === 'other';
    }
}
