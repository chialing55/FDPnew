<div class="page-content people-page-content">
    @forelse ($categories as $category)
        @if ($category->roles->isNotEmpty())
            <section class="content-section people-category">
                <h2 class="content-section__title">{{ $category->title }}</h2>
                <div class="content-section__blocks">
                    @foreach ($category->roles as $role)
                        @php
                            $person = $role->person;
                            $settings = \App\Support\ChangYang\ImageFrame::normalize($person->display_settings);
                            $height = $settings['frame_height'].'px';
                            $positionX = $settings['position_x'].'%';
                            $positionY = $settings['position_y'].'%';
                            $scale = $settings['scale'];
                        @endphp
                        <article class="content-block content-block--image_left has-structured-media">
                            <div class="content-block__media">
                                @if ($person->image_path)
                                    <figure class="has-crop" style="height: {{ $height }}">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($person->image_path) }}" alt="{{ $person->image_alt ?: $person->name }}" style="object-position: {{ $positionX }} {{ $positionY }}; transform: scale({{ $scale }}); transform-origin: {{ $positionX }} {{ $positionY }}">
                                    </figure>
                                @endif
                                @if ($person->contact_html)<div class="content-block__media-content">{!! $person->contact_html !!}</div>@endif
                            </div>
                            <div class="content-block__body">
                                <h3>{{ $person->name }}</h3>
                                @if ($role->role_title)<p class="person-role-title">{{ $role->role_title }}</p>@endif
                                @if ($person->introduction_html)<div class="rich-text">{!! $person->introduction_html !!}</div>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @empty
        <p class="empty-state">This page is being prepared.</p>
    @endforelse
</div>
