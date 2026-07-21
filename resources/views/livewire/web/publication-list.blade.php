<div class="space-y-4">
    @if ($showFilters)
        <div class="grid gap-3 rounded-md bg-gray-50 p-4 sm:grid-cols-3">
            <label class="text-sm font-medium text-gray-700">
                <span class="mb-1 block">{{ app()->getLocale() === 'en' ? 'Year' : '年代' }}</span>
                <select wire:model="year" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">{{ app()->getLocale() === 'en' ? 'All years' : '全部年代' }}</option>
                    @foreach ($years as $yearOption)<option value="{{ $yearOption }}">{{ $yearOption }}</option>@endforeach
                </select>
            </label>
            <label class="text-sm font-medium text-gray-700">
                <span class="mb-1 block">{{ app()->getLocale() === 'en' ? 'Plot' : '樣區' }}</span>
                <select wire:model="site" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">{{ app()->getLocale() === 'en' ? 'All plots' : '全部樣區' }}</option>
                    @foreach ($sites as $siteOption)<option value="{{ $siteOption->id }}">{{ $siteOption->name }}</option>@endforeach
                </select>
            </label>
            <label class="text-sm font-medium text-gray-700">
                <span class="mb-1 block">{{ app()->getLocale() === 'en' ? 'Research topic' : '研究主題' }}</span>
                <select wire:model="subject" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">{{ app()->getLocale() === 'en' ? 'All topics' : '全部研究主題' }}</option>
                    @foreach ($subjects as $subjectOption)<option value="{{ $subjectOption->id }}">{{ $subjectOption->name }}</option>@endforeach
                </select>
            </label>
        </div>
    @endif
    <ul class="divide-y divide-gray-200 bg-white">
        @forelse ($publications as $publication)
            <li class="px-4 py-4">
                <div class="font-medium text-gray-900">{!! $publication->citation_html ?: e($publication->title) !!}</div>
                @if ($publication->sites->isNotEmpty() || $publication->subjects->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @foreach ($publication->sites as $publicationSite)
                            <a href="{{ $publicationSite->page ? url('/' . $publicationSite->page->slug) : '#' }}" class="rounded-full bg-green-50 px-3 py-1 text-forest no-underline hover:bg-green-100">
                                {{ $publicationSite->name }}
                            </a>
                        @endforeach
                        @foreach ($publication->subjects as $publicationSubject)
                            <a href="{{ $publicationSubject->page ? url('/' . $publicationSubject->page->slug) : '#' }}" class="rounded-full bg-amber-50 px-3 py-1 text-amber-800 no-underline hover:bg-amber-100">
                                {{ $publicationSubject->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
                <div class="mt-2 flex flex-wrap gap-4 text-sm">
                    @if ($publication->doi)
                        <a href="{{ 'https://doi.org/' . preg_replace('#^https?://(?:dx\.)?doi\.org/#i', '', $publication->doi) }}" target="_blank" rel="noopener" class="text-forest">DOI</a>
                    @endif
                    @if ($publication->url)
                        <a href="{{ $publication->url }}" target="_blank" rel="noopener" class="text-forest">文章頁面</a>
                    @endif
                    @if ($publication->pdf_path)
                        <a href="{{ Storage::disk('public')->url($publication->pdf_path) }}" target="_blank" rel="noopener" class="text-forest">下載 PDF</a>
                    @endif
                </div>
            </li>
        @empty
            <li class="px-4 py-6 text-gray-600">目前尚無學術產出資料。</li>
        @endforelse
    </ul>
    {{ $publications->links() }}
</div>
