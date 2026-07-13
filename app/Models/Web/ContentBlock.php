<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory;

    protected $table = 'content_blocks';
    protected $connection = 'mysql_web';
    protected $casts = [
        'params' => 'array',
    ];
    protected $fillable = [
        'owner_type',
        'owner_id',
        'title_zh_tw',
        'title_en',
        'body_zh_tw',
        'body_en',
        // 僅供 PageDefault 建立不寫入資料庫的系統固定區塊。
        'view',
        'params',
        'sort_order',
        'is_public',
    ];

    /**
     * 系統固定區塊使用的暫存屬性，不儲存在 content_blocks 資料表。
     */
    public static function systemBlock(array $attributes): self
    {
        return (new self())->forceFill($attributes);
    }

    /** 依語系回傳標題 */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->title_en
            : $this->title_zh_tw;
    }

    /** 依語系回傳內容 */
    public function getBodyAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? $this->body_en
            : $this->body_zh_tw;
    }

    public function owner()
    {
        return $this->morphTo();
    }



}
