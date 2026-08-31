<div class="space-y-4">

    @if ($showSiteFilter || $showSubjectFilter)
        <x-web.filter-bar>

            @if ($showSiteFilter)
                <x-web.filter-select wire:model.live="site" :label="__('web.select_site')"
                    :placeholder="__('web.select_all_site')" :options="$siteOptions" />
            @endif

            @if ($showSubjectFilter)
                <x-web.filter-select wire:model.live="subject" :label="__('web.select_subject')"
                    :placeholder="__('web.select_all_subject')" :options="$subjectOptions" />
            @endif

            <button type="button" wire:click="clearFilters"
                class="rounded-md border px-3 py-2 text-sm hover:bg-gray-50">
                {{ __('web.select_clear') }}
            </button>
        </x-web.filter-bar>
    @endif


    <ul class="divide-y divide-gray-200 bg-white">
        @forelse ($projects as $p)
            <li class="relative cursor-pointer px-4 py-3 transition hover:bg-gray-50 hover:font-bold">
                <a href="{{ route('projects.show', $p) }}" class="absolute inset-0"
                    aria-label="{{ $p->title }}"></a>

                <div>
                    <span class="text-gray-900">

                        {{ $p->title }}
                        @if ($p->start_date)
                            @php
                                $startYear = substr($p->start_date, 0, 4);
                                $endYear = $p->end_date ? substr($p->end_date, 0, 4) : null;
                            @endphp

                            <span class="ml-2 text-xs font-normal text-gray-500">
                                ({{ $startYear }}
                                @if ($endYear && $endYear !== $startYear)
                                    - {{ $endYear }}
                                @endif)
                            </span>
                        @endif
                    </span>
                </div>
                <div class="mt-3 flex w-full flex-col gap-2 text-xs font-normal sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex flex-wrap gap-2 sm:flex-1">
                        @if ($showSiteTags)
                            @foreach ($p->sites->sortBy(fn ($site) => [$site->page?->nav_order ?? PHP_INT_MAX, $site->id]) as $s)
                                <span style="{{ $this->tagStyle('site', $s->id) }}"
                                    class="whitespace-nowrap rounded px-2 py-1 text-gray-700">
                                    {{ $s->name}}
                                </span>
                            @endforeach
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 sm:flex-1 sm:justify-end">
                        @if ($showSubjectTags)
                            @foreach ($p->subjects->sortBy(fn ($subject) => [$subject->page?->nav_order ?? PHP_INT_MAX, $subject->id]) as $sub)
                                <span style="{{ $this->tagStyle('subject', $sub->id) }}"
                                    class="whitespace-nowrap rounded px-2 py-1 text-gray-700">
                                    {{ $sub->short_name ?: $sub->name }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-6 text-gray-600">
                找不到符合條件的計畫。
            </li>
        @endforelse
    </ul>

    <div>
        {{ $projects->links() }}
    </div>
</div>
