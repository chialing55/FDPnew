<div class="space-y-4">
    @if ($showFilters)
        <x-web.filter-bar>
            <x-web.filter-select wire:model.live="year"
                :label="app()->getLocale() === 'en' ? 'Year' : '年代'"
                :placeholder="app()->getLocale() === 'en' ? 'All years' : '全部年代'"
                :options="$years->mapWithKeys(fn ($value) => [(string) $value => $value])->all()" />
            <x-web.filter-select wire:model.live="type"
                :label="app()->getLocale() === 'en' ? 'Type' : '類型'"
                :placeholder="app()->getLocale() === 'en' ? 'All types' : '全部類型'"
                :options="$types->all()" />
            @if ($showSiteFilter)
                <x-web.filter-select wire:model.live="site"
                    :label="__('web.select_site')" :placeholder="__('web.select_all_site')"
                    :options="$siteOptions" />
            @endif
            @if ($showSubjectFilter)
                <x-web.filter-select wire:model.live="subject"
                    :label="__('web.select_subject')" :placeholder="__('web.select_all_subject')"
                    :options="$subjectOptions" />
            @endif
            <button type="button" wire:click="clearFilters"
                class="rounded-md border px-3 py-2 text-sm hover:bg-gray-50">
                {{ __('web.select_clear') }}
            </button>
        </x-web.filter-bar>
    @endif
    <p class="text-sm text-gray-600">
        {{ app()->getLocale() === 'en'
            ? $publications->total() . ' publications'
            : '共有 ' . $publications->total() . ' 篇文獻' }}
    </p>
    <ul class="divide-y divide-gray-200 bg-white">
        @forelse ($publications as $publication)
            <li class="px-4 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="shrink-0 sm:w-24">
                        <span class="inline-block rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">
                            {{ $publication->type_label }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-gray-900">{!! $publication->citation_html ?: e($publication->title) !!}</div>
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
                    </div>
                    <div class="flex shrink-0 flex-wrap justify-end gap-2 text-xs sm:max-w-[35%] sm:pl-4">
                        @if ($showSiteTags)
                            @foreach ($publication->sites as $publicationSite)
                                <a href="{{ $publicationSite->page ? url('/' . $publicationSite->page->slug) : '#' }}"
                                    style="{{ $this->tagStyle('site', $publicationSite->id) }}"
                                    class="rounded px-2 py-1 text-gray-700 no-underline hover:opacity-80">
                                    {{ $publicationSite->name }}
                                </a>
                            @endforeach
                        @endif
                        @if ($showSubjectTags)
                            @foreach ($publication->subjects as $publicationSubject)
                                <a href="{{ $publicationSubject->page ? url('/' . $publicationSubject->page->slug) : '#' }}"
                                    style="{{ $this->tagStyle('subject', $publicationSubject->id) }}"
                                    class="rounded px-2 py-1 text-gray-700 no-underline hover:opacity-80">
                                    {{ $publicationSubject->short_name ?: $publicationSubject->name }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-6 text-gray-600">目前尚無學術產出資料。</li>
        @endforelse
    </ul>
    {{ $publications->links('components.web.compact-pagination') }}
</div>
