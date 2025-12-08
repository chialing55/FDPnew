<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteTeam extends Model
{
    use HasFactory;

    protected $table = 'site_team';
    protected $connection = 'mysql_web';

    // 如果你只用 pivot 不會單獨操作，也可以把 $timestamps = false;
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'team_id',
        'role',        // 例如 'PI', 'co-PI', 'partner'，看你資料表是否有
        'sort_order',  // 0/1 是否主責單位
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
