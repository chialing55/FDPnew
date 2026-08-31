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
        @forelse ($outputs as $o)
            <li class="relative cursor-pointer px-4 py-3 transition hover:bg-gray-50 hover:font-bold">
                <a href="{{ url('results/' . $o->slug) }}" class="absolute inset-0"
                    aria-label="{{ $o->title }}"></a>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-gray-900">
                        {{ $o->title }}
                    </span>
                    <div class="flex flex-wrap justify-end gap-2 text-xs sm:ml-auto sm:pl-4">
                        @if ($showSiteTags)
                            @foreach ($o->sites as $s)
                                <span style="{{ $this->tagStyle('site', $s->id) }}"
                                    class="{{ $this->tagClasses() }}">
                                    {{ $s->name }}
                                </span>
                            @endforeach
                        @endif

                        @if ($showSubjectTags)
                            @foreach ($o->subjects->sortBy('nav_order') as $sub)
                                <span style="{{ $this->tagStyle('subject', $sub->id) }}"
                                    class="{{ $this->tagClasses() }}">
                                    {{ $sub->short_name ?: $sub->name }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </li>

        @empty
            <li class="px-4 py-6 text-gray-600">
                找不到符合條件的研究成果。
            </li>
        @endforelse
    </ul>

    <div>
        {{ $outputs->links() }}
    </div>
</div>
