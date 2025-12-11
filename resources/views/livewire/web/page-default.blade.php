@php($contentBlocks = $contentBlocks ?? collect())

<div class="flex flex-col gap-8 lg:flex-row">
    {{-- 左側目錄 --}}
    <aside class="rounded-lg bg-gray-100 p-4 lg:w-1/4 lg:pr-4">
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
                    <h1
                        class="relative mt-0 flex items-center gap-2 bg-forest-canopy p-2 pl-4 text-2xl font-bold text-white before:absolute before:left-0 before:top-0 before:h-full before:w-2 before:bg-yellow-400 rounded-lg">
                        {{ $block->title }}
                    </h1>
                @endif

                @if (!empty($block->body))
                    <div class="prose prose-sm max-w-none">
                        {!! $block->body !!}
                    </div>
                @endif
                @if (!empty($block->view))
                    @livewire($block->view, $block->params ?? [])
                @endif
            </div>
        @endforeach
    </section>

</div>
