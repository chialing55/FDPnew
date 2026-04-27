@php($contentBlocks = $contentBlocks ?? collect())
@php($showSidebar = $contentBlocks->count() >= 4)

<div class="flex flex-col gap-8 rounded-lg bg-white p-6 lg:flex-row">

    {{-- 左側目錄（只有在區塊數 ≥ 3 時才出現） --}}
    @if ($showSidebar)
        <aside class="hidden rounded-lg p-4 lg:block lg:w-[20%] lg:pr-4">
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
    @endif


    {{-- 右側內容 --}}
    <section
        class="@if ($showSidebar) lg:w-[80%]
            @else
                w-full    {{-- <3 個 → 置中 + 全寬 --}} @endif space-y-8">
        @foreach ($contentBlocks as $block)
            <div id="{{ $block->anchorId }}" class="m-2 scroll-mt-24">
                @if (!empty($block->title) || !empty($block->body) || !empty($block->view))
                    <div class="rounded-md border border-gray-300 bg-white px-6 py-4">
                        <div class="items-start gap-6 md:grid md:grid-cols-[auto_1px_minmax(0,1fr)]">

                            {{-- 左側：標題 --}}
                            @if (!empty($block->title))
                                <div class="mb-2 flex items-start pr-4">
                                    {{-- 左側黃色 Bar --}}
                                    <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>

                                    {{-- 標題 --}}
                                    <div
                                        class="min-w-[6rem] max-w-[10rem] whitespace-normal font-semibold leading-relaxed text-gray-800">
                                        {{ $block->title }}
                                    </div>
                                </div>
                            @else
                                <div></div>
                            @endif


                            {{-- 中間直線 --}}
                            <div class="mx-auto hidden h-full w-px bg-gray-300 md:block"></div>

                            {{-- 右側內容 --}}
                            <div class="space-y-4">
                                @if (!empty($block->body))
                                    <div class="prose prose-sm max-w-none">
                                        {!! $block->body !!}
                                    </div>
                                @endif

                                @if (!empty($block->view))
                                    @livewire($block->view, $block->params ?? [])
                                @endif
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        @if (($slug ?? null) === 'plants')
            <div class="m-2 scroll-mt-24">
                <div class="rounded-md border border-gray-300 bg-white px-6 py-4">
                    <div class="items-start gap-6 md:grid md:grid-cols-[auto_1px_minmax(0,1fr)]">
                        <div class="mb-2 flex items-start pr-4">
                            <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>
                            <div class="min-w-[6rem] max-w-[10rem] whitespace-normal font-semibold leading-relaxed text-gray-800">
                                福山樣區植物名錄
                            </div>
                        </div>

                        <div class="mx-auto hidden h-full w-px bg-gray-300 md:block"></div>

                        <div class="space-y-4">
                            <div class="prose prose-sm max-w-none">
                                <p>
                                    <a href="{{ url('/web/splist') }}" target="_blank" rel="noopener">
                                        前往福山樣區植物名錄
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

</div>
