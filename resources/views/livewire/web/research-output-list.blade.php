<div class="space-y-4">
    @if ($showSiteFilter || $showSubjectFilter)
        <div class="mb-4 flex flex-col gap-3 p-3 sm:flex-row sm:items-end">

            @if ($showSiteFilter)
                <div class="w-full sm:w-64">
                    <label class="mb-1 block text-xs text-gray-600">
                        {{ __('web.select_site') }}
                    </label>
                    <select wire:model.live="site"
                        class="h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{{ __('web.select_all_site') }}</option>
                        @foreach ($siteOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($showSubjectFilter)
                <div class="w-full sm:w-64">
                    <label class="mb-1 block text-xs text-gray-600">
                        {{ __('web.select_subject') }}
                    </label>
                    <select wire:model.live="subject"
                        class="h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{{ __('web.select_all_subject') }}</option>
                        @foreach ($subjectOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="button" wire:click="clearFilters"
                class="rounded-md border px-3 py-2 text-sm hover:bg-gray-50">
                {{ __('web.select_clear') }}
            </button>
        </div>
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
                                    class="rounded px-2 py-1 text-gray-700">
                                    {{ $s->name }}
                                </span>
                            @endforeach
                        @endif

                        @if ($showSubjectTags)
                            @foreach ($o->subjects->sortBy('nav_order') as $sub)
                                <span style="{{ $this->tagStyle('subject', $sub->id) }}"
                                    class="rounded px-2 py-1 text-gray-700">
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
