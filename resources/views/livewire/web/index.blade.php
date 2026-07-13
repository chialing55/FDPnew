<div class='px-0'>
    <div class='mb-4 mt-4 flex justify-center space-x-4 font-bold md:space-x-8 md:text-4xl'>
        <div>{{ count($plots) }} {{ __('web.index_text_1') }}</div>
        <div>100 {{ __('web.index_text_2') }}</div>
        <div>1000000 {{ __('web.index_text_3') }}</div>
    </div>
    <div class='z-5 mx-auto gap-4 text-center md:px-8 md:pb-4'>
        <div class="web-content text-sm leading-relaxed text-gray-700 md:text-base">
            {!! $indexIntro->body ?? '' !!}
        </div>
    </div>
    {{-- plots --}}

    <div class='relative left-1/2 right-1/2 mb-12 ml-[-50vw] mr-[-50vw] bg-gray-200 py-6 lg:mt-8' style='width: 100vw;'>
        <div class="pointer-events-none absolute inset-0 z-10">
            <div class="sticky bottom-0 h-full w-full"
                style="
      background-image: url('{{ asset('images/background/森林底圖手繪風3.png') }}');
      background-size: cover;
      background-position: bottom;
      background-repeat: no-repeat;
      opacity: 0.1;
    ">
            </div>
        </div>
        <div class='relative z-20 mx-auto lg:-mt-16 lg:max-w-[70rem]'>
            @foreach ($plots as $plot)
                <a href="{{ url($plotsContent[$plot]['slug']) }}"
                    class="{{ $loop->even ? 'lg:flex-row-reverse' : '' }} group m-4 block overflow-hidden rounded-lg border bg-white p-2 !font-normal !no-underline transition-all duration-300 hover:-translate-y-1 hover:!font-normal hover:!no-underline hover:shadow-xl lg:flex lg:flex-row">
                    @if ($plotsContent[$plot]['image'])
                        <div class='relative min-h-48 self-stretch overflow-hidden rounded-lg lg:w-[60%]'>
                            <img src='{{ $plotsContent[$plot]['image'] }}' alt='{{ $plot }}'
                                class='absolute inset-0 h-full w-full rounded-lg object-cover'
                                style='object-position: center {{ $plotsContent[$plot]['image_position'] }}%;'>
                        </div>
                    @endif
                    <div class='p-4 text-left {{ $plotsContent[$plot]['image'] ? 'lg:w-[40%]' : 'lg:w-full' }} lg:max-w-lg'>
                        <h1 class='{{ $loop->even ? '' : 'text-right' }} mt-0 text-2xl capitalize md:text-5xl'
                            style='text-shadow: 1px 1px 4px rgba(51, 77, 43, 0.7); line-height:2rem;'>
                            {{ $plotsContent[$plot]['title'] ?? '' }} </h1>
                        <div class='web-content text-sm text-gray-600'>
                            {!! $plotsContent[$plot]['intro'] ?? '' !!}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- news --}}
    <div>
        <h1 class='inline-block bg-forest-dark p-2 text-white'>{{ __('web.index_news') }}</h1>
        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($latestNews as $news)
                <a href="{{ $news->external_url ?: route('news.show', $news) }}"
                    @if ($news->external_url) target="_blank" rel="noopener" @endif
                    class="group overflow-hidden rounded-lg border bg-white text-gray-900 no-underline shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:no-underline">
                    @if ($news->cover_image)
                        <img src="{{ Storage::disk('public')->url($news->cover_image) }}" alt="" class="h-40 w-full object-cover">
                    @endif
                    <div class="p-4">
                        <div class="text-xs text-gray-500">{{ $news->publish_date }}</div>
                        <h2 class="mt-1 text-lg font-semibold group-hover:text-forest">{{ $news->title }}</h2>
                    </div>
                </a>
            @empty
                <p class="col-span-full py-6 text-gray-500">目前尚無最新消息。</p>
            @endforelse
        </div>
    </div>



</div>
