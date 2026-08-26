<?php

namespace App\Models\ChangYang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockImage extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'changyang_block_images';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['display_settings' => 'array', 'sort_order' => 'integer'];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class, 'content_block_id');
    }
}
