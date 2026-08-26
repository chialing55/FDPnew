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
        'authors_zh_tw',
        'title',
        'title_zh_tw',
        'year',
        'journal',
        'journal_zh_tw',
        'volume',
        'issue',
        'pages',
        'pdf_path',
        'doi',
        'url',
        'external_id',
        'type',
        'language',
        'institution',
        'institution_zh_tw',
        'thesis_type',
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

    public function getDisplayAuthorsAttribute(): ?string
    {
        return $this->localizedValue('authors');
    }

    public function getDisplayTitleAttribute(): ?string
    {
        return $this->localizedValue('title');
    }

    public function getDisplayJournalAttribute(): ?string
    {
        return $this->localizedValue('journal');
    }

    public function getDisplayInstitutionAttribute(): ?string
    {
        return $this->localizedValue('institution');
    }

    /** 依語系回傳引用文字 */
    public function getCitationAttribute(): ?string
    {
        return strip_tags($this->citation_html ?? '');
    }

    public function getCitationHtmlAttribute(): ?string
    {
        $authors = filled($this->display_authors) ? e(rtrim($this->abbreviated_authors, '.')) : null;
        $title = filled($this->display_title) ? e(rtrim($this->display_title, '.')) : null;
        if ($title !== null && app()->getLocale() === 'en' && $this->isChineseLanguage()) {
            $title .= ' (in Chinese)';
        }
        $year = filled($this->year) ? '<strong>'.e($this->year).'</strong>' : null;
        $source = $this->thesisSource();
        if ($source === null) {
            $source = filled($this->display_journal) ? '<em>'.e(rtrim($this->display_journal, '.')).'</em>' : '';
        }
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

    private function thesisSource(): ?string
    {
        if ($this->type !== 'thesis') {
            return null;
        }

        $thesisType = match (strtolower(trim((string) $this->thesis_type))) {
            'master', "master's thesis", 'masters thesis', '碩士', '碩士論文' => 'master',
            'doctoral', 'doctoral dissertation', 'phd', 'ph.d.', '博士', '博士論文' => 'doctoral',
            default => null,
        };

        $label = app()->getLocale() === 'en'
            ? match ($thesisType) {
                'master' => "Master's thesis",
                'doctoral' => 'Doctoral dissertation',
                default => 'Thesis',
            }
            : match ($thesisType) {
                'master' => '碩士論文',
                'doctoral' => '博士論文',
                default => '學位論文',
            };

        return filled($this->display_institution)
            ? $label.', '.e(rtrim($this->display_institution, '.'))
            : $label;
    }

    private function isChineseLanguage(): bool
    {
        return in_array(strtolower(trim((string) $this->language)), [
            'zh',
            'zh-tw',
            'zh_tw',
            'chinese',
            '中文',
        ], true);
    }

    /** 保留首尾作者，避免作者群過長撐開列表與引用內容。 */
    public function getAbbreviatedAuthorsAttribute(): ?string
    {
        if (! filled($this->display_authors)) {
            return null;
        }

        $authors = preg_split('/\s*;\s*/u', trim($this->display_authors), -1, PREG_SPLIT_NO_EMPTY);

        if (count($authors) <= 5) {
            return implode('; ', $authors);
        }

        return implode('; ', array_slice($authors, 0, 3))
            .'; ....; '
            .implode('; ', array_slice($authors, -2));
    }

    private function localizedValue(string $field): ?string
    {
        if (app()->getLocale() !== 'en' && filled($this->getAttribute("{$field}_zh_tw"))) {
            return $this->getAttribute("{$field}_zh_tw");
        }

        return $this->getAttribute($field);
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
