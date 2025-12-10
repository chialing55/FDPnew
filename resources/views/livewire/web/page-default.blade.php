@php($contentBlocks = $contentBlocks ?? collect())

<div class="flex flex-col gap-8 lg:flex-row">
    {{-- 左側目錄 --}}
    <aside class="lg:w-1/4 lg:pr-4 bg-gray-100 p-4 rounded-lg">
        <div class="sticky top-24 space-y-2">
            @foreach ($contentBlocks as $block)
                @if (!empty($block->title))
                    <a href="#{{ $block->anchorId }}"
                        class="block rounded px-3 py-2 text-sm text-gray-700 no-underline transition hover:bg-forest-mist hover:text-forest hover:no-underline">
                        {{ $block->title }}
                    </a>
                @endif
            @endforeach
        </div>
    </aside>


    {{-- 右側內容 --}}
    <section class="space-y-8 lg:w-3/4">
        @foreach ($contentBlocks as $block)
            <div id="{{ $block->anchorId }}" class="scroll-mt-24">
                @if (!empty($block->title))
                    <h1 class="mt-0 flex items-center gap-2 border-b border-forest pb-2 text-2xl font-bold">
                        <svg class="h-5 w-5 text-forest" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l9 4 9-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9 4 9-4" />
                        </svg>
                        {{ $block->title }}
                    </h1>
                @endif

                @if (!empty($block->body))
                    <div class="prose prose-sm max-w-none">
                        {!! $block->body !!}
                    </div>
                @endif
            </div>
        @endforeach
    </section>

</div>
