<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonRole extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_person_roles';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'ended_on' => 'date', 'is_current' => 'boolean', 'sort_order' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PersonCategory::class, 'category_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
