<div class='px-0'>
    <div class='mb-4 mt-4 flex justify-center space-x-4 font-bold md:space-x-8 md:text-4xl'>
        <div>{{ count($plots) }} {{ __('web.index_text_1') }}</div>
        <div>100 {{ __('web.index_text_2') }}</div>
        <div>1000000 {{ __('web.index_text_3') }}</div>
    </div>
    <div class='z-5 mx-auto gap-4 text-center md:px-8 md:pb-4'>
        <p class="text-sm leading-relaxed text-gray-700 md:text-base">
            {!! $indexIntro->body ?? '' !!}
        </p>
    </div>
    {{-- plots --}}
    <div class='relative left-1/2 right-1/2 mb-12 ml-[-50vw] mr-[-50vw] w-screen bg-gray-200 py-6 lg:mt-8'>
        <div class='mx-auto lg:-mt-16 lg:max-w-[70rem]'>
            @foreach ($plots as $plot)
                @php
                    $img_postion = match ($plot) {
                        'fushan' => 'object-top md:object-[0%_-50px]',
                        'nanjenshan' => 'object-center',
                        'shoushan' => 'object-top',
                        default => 'object-center',
                    };

                @endphp

                <div
                    class="{{ $loop->even ? 'lg:flex-row-reverse' : '' }} m-4 overflow-hidden rounded-lg border bg-white p-2 lg:flex lg:flex-row">

                    <img src='{{ asset("images/plots/{$plot}_thumb.jpg") }}' alt='{{ $plot }}'
                        class='{{ $img_postion }} h-48 w-full object-cover lg:w-[60%]'>

                    <div class='{{ $loop->even ? 'pr-4' : 'pl-4' }}'>
                        <h2 class='{{ $loop->even ? 'text-right' : '' }} text-2xl capitalize'>
                            {{ __('web.nav_plots_' . $plot) }}
                        </h2>
                        <p class='text-sm text-gray-600'>
                            {{ __('web.plots_' . $plot . '_description') }}
                        </p>
                    </div>

                </div>
            @endforeach



        </div>
    </div>

    {{-- news --}}
    <div>
        <h1 class='inline-block bg-forest-dark p-2 text-white'>{{ __('web.nav_news') }}</h1>

    </div>



</div>
