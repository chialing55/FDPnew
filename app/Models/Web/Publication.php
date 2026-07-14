<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $table = 'publications';
    protected $connection = 'mysql_web';

    protected $fillable = [
        'authors',
        'title',
        'year',
        'journal',
        'volume',
        'issue',
        'pages',
        'pdf_path',
        'doi',
        'url',
        'external_id',
        'type',
        'is_open_access',
        'is_active',

    ];

    /** 依語系回傳引用文字 */
    public function getCitationAttribute(): ?string
    {
        return strip_tags($this->citation_html ?? '');
    }

    public function getCitationHtmlAttribute(): ?string
    {
        $authors = filled($this->authors) ? e(rtrim($this->authors, '.')) : null;
        $title = filled($this->title) ? e(rtrim($this->title, '.')) : null;
        $year = filled($this->year) ? '<strong>' . e($this->year) . '</strong>' : null;
        $source = filled($this->journal) ? '<em>' . e(rtrim($this->journal, '.')) . '</em>' : '';
        if (filled($this->volume)) {
            $source .= ($source !== '' ? ' ' : '') . e($this->volume);
        }
        if (filled($this->issue)) {
            $source .= '(' . e($this->issue) . ')';
        }
        if (filled($this->pages)) {
            $source .= ($source !== '' ? ': ' : '') . e($this->pages);
        }

        $parts = SiteSetting::getValue('publication_citation_style', 'year_after_authors') === 'year_at_end'
            ? array_filter([$authors, $title, $source ?: null, $year ? '(' . $year . ')' : null])
            : array_filter([$authors, $year, $title, $source ?: null]);

        return $parts === [] ? null : implode('. ', $parts) . '.';
    }

    /** scope：依年份倒序 */
    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('year');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'publication_site')
            ->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'publication_subject')
            ->withTimestamps();
    }
}
