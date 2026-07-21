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

    public static function typeLabels(?string $locale = null): array
    {
        $english = ($locale ?? app()->getLocale()) === 'en';

        return $english
            ? [
                'book' => 'Book',
                'thesis' => 'Thesis',
                'dataset' => 'Dataset',
                'journalArtical' => 'Journal article',
                'journalArticle' => 'Journal article',
                'journalartical' => 'Journal article',
                'journalarticle' => 'Journal article',
                'paper' => 'Paper',
                'poster' => 'Poster',
                'oral' => 'Oral presentation',
            ]
            : [
                'book' => '書籍',
                'thesis' => '學位論文',
                'dataset' => '資料集',
                'journalArtical' => '期刊論文',
                'journalArticle' => '期刊論文',
                'journalartical' => '期刊論文',
                'journalarticle' => '期刊論文',
                'paper' => '論文',
                'poster' => '海報發表',
                'oral' => '口頭發表',
            ];
    }

    public function getTypeLabelAttribute(): string
    {
        return static::typeLabels()[$this->type] ?? $this->type ?? '';
    }

    /** 依語系回傳引用文字 */
    public function getCitationAttribute(): ?string
    {
        return strip_tags($this->citation_html ?? '');
    }

    public function getCitationHtmlAttribute(): ?string
    {
        $authors = filled($this->authors) ? e(rtrim($this->abbreviated_authors, '.')) : null;
        $title = filled($this->title) ? e(rtrim($this->title, '.')) : null;
        $year = filled($this->year) ? '<strong>'.e($this->year).'</strong>' : null;
        $source = filled($this->journal) ? '<em>'.e(rtrim($this->journal, '.')).'</em>' : '';
        if (filled($this->volume)) {
            $source .= ($source !== '' ? ' ' : '').e($this->volume);
        }
        if (filled($this->issue)) {
            $source .= '('.e($this->issue).')';
        }
        if (filled($this->pages)) {
            $source .= ($source !== '' ? ': ' : '').e($this->pages);
        }

        $parts = SiteSetting::getValue('publication_citation_style', 'year_after_authors') === 'year_at_end'
            ? array_filter([$authors, $title, $source ?: null, $year ? '('.$year.')' : null])
            : array_filter([$authors, $year, $title, $source ?: null]);

        return $parts === [] ? null : implode('. ', $parts).'.';
    }

    /** 保留首尾作者，避免作者群過長撐開列表與引用內容。 */
    public function getAbbreviatedAuthorsAttribute(): ?string
    {
        if (! filled($this->authors)) {
            return null;
        }

        $authors = preg_split('/\s*;\s*/u', trim($this->authors), -1, PREG_SPLIT_NO_EMPTY);

        if (count($authors) <= 5) {
            return implode('; ', $authors);
        }

        return implode('; ', array_slice($authors, 0, 3))
            .'; ....; '
            .implode('; ', array_slice($authors, -2));
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
