<div class="seeds-unknown-page">
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
    <h2>UNKNOWN</h2>

    <div class="unknown-toolbar">
        <select name="unk" class="fs100" wire:model='unk' wire:change="search">
            <option value="%">全部種類</option>
            @foreach ($unklist as $unkOption)
                <option value="{{ $unkOption }}">{{ $unkOption }}</option>
            @endforeach
        </select>

        <button type="button" class="datasavebutton unknown-action-button" wire:click="openCreateUnknown">
            新增 UNKNOWN
        </button>
    </div>

    @include('livewire.fushan.partials.unknown-editor-panel')

    <div class="unknown-list-section">
        <div class="unknown-card-grid unknown-original-grid">
            @foreach($unkdes as $unk)
            <div class='photocombo text_box unknown-card unknown-original-card {{ $editingUnkName === $unk['unkname'] ? 'is-active' : '' }}' wire:key="unknown-card-{{ $unk['unkname'] }}">
                <h6>{{$unk['unkname']}} 
                    @if((int) (auth()->user()?->is_admin ?? 0) === 1)
                        <a class="unknown-card-button admin-only-body-link" href="{{ route('admin.fushan.seeds.unknown.data', ['unk' => $unk['unkname']]) }}">檢視資料</a>
                    @endif
                    <button type="button" class="unknown-card-button" wire:click="openEditor('{{ $unk['unkname'] }}')">編輯說明</button>
                </h6>
                <hr>
                <div class='unknown-description'>{{$unk['des']}}</div>

                <div class="unknown-photo-row">
                    @forelse(($unkphoto[$unk['unkname']] ?? []) as $photo)
                    <div class="unknown-photo-item" wire:key="unknown-photo-{{ $photo['id'] }}">
                        <div class='photocombo'>
                            <div class='photo'>
                                <a href='{{ asset("FDPfiles/splist/photo/unknown/{$photo['unkname']}/{$photo['filename']}") }}' data-fancybox="gallery" data-caption="{{$unk['unkname']}}<br> 類型: {{$codelist[$photo['code']] ?? ''}}@if($photo['des']!='')<br>{{$photo['des']}}
                        @endif" >
                                    <img src="{{ asset("FDPfiles/splist/photo/unknown/{$photo['unkname']}/s_{$photo['filename']}") }}" width="230">
                                </a>
                            </div>

                            <div>
                                類型: {{$codelist[$photo['code']] ?? ''}}
                                @if($photo['des']!='')
                                    <br>{{$photo['des']}}
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="unknown-empty-note">尚無照片</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>


    </div>
    
</div>

@script
<script>
    $wire.on('unknown-editor-opened', () => {
        setTimeout(() => {
            document.getElementById('unknown-editor-panel')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 80);
    });
</script>
@endscript
