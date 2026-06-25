<div style="display: grid; gap: 18px; max-width: 900px;">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <strong>樣區</strong>
        <select wire:model.live="site" class="fs100" style="width:auto; min-width:180px;">
            @foreach ($siteOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @if ($site !== 'fushan')
        <div style="padding: 12px 14px; border: 1px solid #e4d18a; background: #fff8d7;">
            暫未開放下載。
        </div>
    @else
        <div style="display:grid; gap:10px;">
            <strong>資料類型</strong>
            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                @foreach ($dataTypeOptions as $key => $option)
                    <label style="display:inline-flex; align-items:center; gap:6px;">
                        <input type="checkbox" wire:model.live="selectedTypes" value="{{ $key }}">
                        {{ $option['label'] }}
                    </label>
                @endforeach
            </div>
        </div>

        @foreach ($dataTypeOptions as $key => $option)
            @if (in_array($key, $selectedTypes, true))
                @php($dateOptions = $this->dateOptions($key))
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding:12px 14px; border:1px solid #d8dec0; background:#fff;">
                    <strong>{{ $option['range_label'] }}</strong>

                    <label for="catalog-{{ $key }}-start">從</label>
                    <select id="catalog-{{ $key }}-start" wire:model="ranges.{{ $key }}.start" class="fs100" style="width:auto; min-width:210px;">
                        @forelse ($dateOptions as $dateOption)
                            <option value="{{ $dateOption['census'] }}">{{ $dateOption['label'] }}</option>
                        @empty
                            <option value="">尚無資料</option>
                        @endforelse
                    </select>

                    <label for="catalog-{{ $key }}-end">到</label>
                    <select id="catalog-{{ $key }}-end" wire:model="ranges.{{ $key }}.end" class="fs100" style="width:auto; min-width:210px;">
                        @forelse ($dateOptions as $dateOption)
                            <option value="{{ $dateOption['census'] }}">{{ $dateOption['label'] }}</option>
                        @empty
                            <option value="">尚無資料</option>
                        @endforelse
                    </select>
                </div>
            @endif
        @endforeach

        <div>
            <button type="button" class="recruitbutton" wire:click="download" wire:loading.attr="disabled">
                下載 Excel
            </button>
            <button type="button" class="recruitbutton" wire:click="downloadWord" wire:loading.attr="disabled" style="margin-left: 8px;">
                下載 Word
            </button>
            <span wire:loading wire:target="download,downloadWord" style="margin-left: 10px;">資料整理中...</span>
        </div>

        @if ($message !== '')
            <div style="padding: 12px 14px; border: 1px solid #e2a3a3; background: #fff1f1; color: #9b1c1c;">
                {{ $message }}
            </div>
        @endif
    @endif

    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
</div>
