<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_people';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['display_settings' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(PersonRole::class, 'person_id')->orderBy('sort_order');
    }
}
