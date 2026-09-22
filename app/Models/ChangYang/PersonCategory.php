<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonCategory extends Model
{
    protected $connection = 'mysql_web';

    protected $table = 'changyang_person_categories';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(PersonRole::class, 'category_id');
    }
}
