<div>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    <style>
        .plant-photo-heading h2 {
            margin: 0 0 12px;
            color: #2f3d24;
            font-size: 24px;
        }

        .plant-photo-heading-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
        }

        .plant-photo-heading p {
            margin: 0 0 24px;
            color: #52604b;
            line-height: 1.6;
        }

        .plant-photo-front-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 6px 18px;
            border: 1px solid #cbd5c0;
            border-radius: 5px;
            background: #f7f6d9;
            color: #334155;
            text-decoration: none !important;
            white-space: nowrap;
        }

        .plant-photo-front-link:hover,
        .plant-photo-front-link:focus,
        .plant-photo-front-link:active,
        .plant-photo-front-link:visited {
            color: #334155;
            text-decoration: none !important;
        }

        .plant-photo-editor-panel {
            display: grid;
            gap: 18px;
        }

        .plant-photo-message {
            margin: 16px 0 12px;
            padding: 10px 12px;
            color: #1f4d2f;
            border: 1px solid #9ec7a5;
            border-radius: 5px;
            background: #eef8ef;
        }

        .plant-photo-dis-note-box {
            box-sizing: border-box;
            width: 100%;
            margin: 18px 0;
            padding: 18px 24px;
            border-radius: 6px;
            background-color: #fffbeb;
        }

        .plant-photo-dis-note-title {
            display: flex;
            align-items: stretch;
            gap: 12px;
            margin-bottom: 16px;
        }

        .plant-photo-dis-note-bar {
            width: 5px;
            background: #fbbf24;
        }

        .plant-photo-dis-note-title h6 {
            margin: 0;
            font-size: 18px;
            color: #17252a;
        }

        .plant-photo-dis-note-title span {
            margin-left: 24px;
            color: #374151;
            font-size: 14px;
            font-weight: 400;
        }

        .plant-photo-dis-note-list {
            display: grid;
            gap: 12px;
        }

        .plant-photo-dis-note-form {
            display: grid;
            grid-template-columns: 130px 130px minmax(360px, 1fr) auto;
            gap: 12px;
            align-items: start;
        }

        .plant-photo-dis-note-form label {
            display: grid;
            gap: 5px;
            color: #374151;
            font-size: 13px;
        }

        .plant-photo-dis-note-form select,
        .plant-photo-dis-note-form input,
        .plant-photo-dis-note-form textarea {
            box-sizing: border-box;
            width: 100%;
            border: 1px solid #cbd5c0;
            border-radius: 4px;
            padding: 9px 12px;
            font-size: 15px;
            background: #fff;
        }

        .plant-photo-dis-note-form select,
        .plant-photo-dis-note-form input {
            height: 48px;
        }

        .plant-photo-dis-note-form textarea {
            height: 48px;
            min-height: 48px;
            resize: vertical;
        }

        .plant-photo-dis-note-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            height: 48px;
            margin: 24px 0 0;
        }

        .plant-photo-editor {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            gap: 18px;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
        }

        .plant-photo-editor-image {
            display: grid;
            gap: 8px;
            align-content: start;
        }

        .plant-photo-editor-image img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 2.6;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f8f8f8;
        }

        .plant-photo-editor-image span {
            display: block;
            color: #6b7280;
            font-size: 90%;
            overflow-wrap: anywhere;
        }

        .plant-photo-editor-fields {
            display: grid;
            grid-template-columns: repeat(4, 160px);
            gap: 12px;
            align-content: start;
        }

        .plant-photo-editor-fields label {
            display: grid;
            gap: 5px;
            color: #374151;
            font-size: 13px;
        }

        .plant-photo-editor-fields input,
        .plant-photo-editor-fields select,
        .plant-photo-editor-fields textarea {
            box-sizing: border-box;
            width: 100%;
            border: 1px solid #cbd5c0;
            border-radius: 4px;
            padding: 9px 12px;
            font-size: 15px;
        }

        .plant-photo-editor-fields input,
        .plant-photo-editor-fields select {
            min-height: 48px;
        }

        .plant-photo-editor-fields textarea {
            height: 48px;
            min-height: 48px;
            resize: vertical;
        }

        .plant-photo-editor-wide,
        .plant-photo-editor-actions {
            grid-column: 1 / -1;
        }

        .plant-photo-editor-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            margin: 0;
        }

        .plant-photo-delete-button {
            padding: 7px 18px;
            border: 0;
            border-radius: 4px;
            color: #fff;
            background: #8b705c;
            cursor: pointer;
            font-weight: 700;
        }

        .plant-photo-save-button {
            width: auto !important;
            min-width: 0;
            padding: 6px 18px;
        }

        .plant-photo-upload-card,
        .plant-photo-empty {
            padding: 12px;
            border: 1px solid #d8dfd4;
            border-radius: 5px;
            background: #fff;
        }

        .plant-photo-dropzone {
            display: grid;
            place-items: center;
            min-height: 72px;
            border: 1px dashed #aebca8;
            border-radius: 5px;
            background: #f7faf4;
            color: #52604b;
            text-align: center;
            cursor: pointer;
        }

        .plant-photo-dropzone input {
            display: none;
        }

        .plant-photo-dropzone small {
            display: block;
            margin-top: 6px;
            color: #6b7280;
        }

        .plant-photo-form-error {
            margin: 8px 0 0;
            color: #a33d2c;
        }

        @media (max-width: 900px) {

            .plant-photo-editor,
            .plant-photo-editor-fields,
            .plant-photo-dis-note-form {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="plant-photo-heading-row">
        <div class="plant-photo-heading">
            <h2>
                <i>{{ $speciesinfo['now_simname'] ?? $spcode }}</i>
                <span style="margin-left: 16px;">{{ $speciesinfo['csp'] ?? '' }}</span>
            </h2>
            <p>
                {{ $spcode }}
                <span style="margin-left: 16px;">{{ $speciesinfo['apgfamily'] ?? '' }}
                    {{ $speciesinfo['chapgfamily'] ?? '' }}</span>
            </p>
        </div>
        <a class="plant-photo-front-link" href="{{ route('front.species', ['spcode' => $catalogCode]) }}" target="_blank" rel="noopener">
            前台頁面
        </a>
    </div>

    <div class="plant-photo-dis-note-box">
        <h6>辨識要點<span style='padding-left:20px;'>*種子雨收集與小苗調查用</span></h6>
        <datalist id="plant-photo-dis-note-type2-options">
            @foreach ($disNoteType2Options as $type2)
                <option value="{{ $type2 }}"></option>
            @endforeach
        </datalist>

        <div class="plant-photo-dis-note-list">
            @forelse($editingDisNotes as $noteKey => $note)
                <form wire:submit.prevent="saveDisNote('{{ $noteKey }}')" class="plant-photo-dis-note-form"
                    wire:key="plant-dis-note-{{ $noteKey }}">
                    <label>
                        類型
                        <select wire:model.defer="editingDisNotes.{{ $noteKey }}.type">
                            <option value="">請選擇</option>
                            @foreach ($disNoteTypeOptions as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        種子雨分類(可留空)
                        <input type="text" list="plant-photo-dis-note-type2-options"
                            wire:model.defer="editingDisNotes.{{ $noteKey }}.type2">
                    </label>

                    <label>
                        說明
                        <textarea rows="1" wire:model.defer="editingDisNotes.{{ $noteKey }}.note"></textarea>
                    </label>

                    <p class="plant-photo-dis-note-actions">
                        <button type="submit" class="datasavebutton plant-photo-save-button">修改</button>
                        <button type="button" class="plant-photo-delete-button"
                            wire:click="deleteDisNote('{{ $noteKey }}')"
                            onclick="return confirm('確定刪除這筆辨識要點？')">刪除</button>
                    </p>
                    @if (!empty($disNoteMessages[$noteKey]))
                        <p class="plant-photo-message" style="grid-column: 1 / -1;"
                            wire:key="dis-note-message-{{ $noteKey }}-{{ $messageVersion }}"
                            x-data="{ show: true }" x-show="show" x-transition.opacity
                            x-init="setTimeout(() => show = false, 3000)">
                            {{ $disNoteMessages[$noteKey] }}
                        </p>
                    @endif
                </form>
            @empty
                <p style="margin: 0; color: #52604b;">目前尚無辨識要點。</p>
            @endforelse

            <form wire:submit.prevent="createDisNote" class="plant-photo-dis-note-form">
                <label>
                    類型
                    <select wire:model.defer="newDisNoteType">
                        <option value="">請選擇</option>
                        @foreach ($disNoteTypeOptions as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    種子雨分類(可留空)
                    <input type="text" list="plant-photo-dis-note-type2-options" wire:model.defer="newDisNoteType2">
                </label>

                <label>
                    說明
                    <textarea rows="1" wire:model.defer="newDisNoteNote"></textarea>
                </label>

                <p class="plant-photo-dis-note-actions">
                    <button type="submit" class="datasavebutton plant-photo-save-button">新增</button>
                </p>
            </form>
        </div>
        @if ($disNoteMessage)
            <p class="plant-photo-message" wire:key="dis-note-message-{{ $messageVersion }}"
                x-data="{ show: true }" x-show="show" x-transition.opacity
                x-init="setTimeout(() => show = false, 3000)">{{ $disNoteMessage }}</p>
        @endif
    </div>

    <div class="plant-photo-editor-panel">
        <datalist id="plant-photo-type2-options">
            @foreach ($type2Options as $type2)
                <option value="{{ $type2 }}"></option>
            @endforeach
        </datalist>

        @forelse($editingPhotos as $photoKey => $photo)
            <form wire:submit.prevent="savePhoto('{{ $photoKey }}')" class="plant-photo-editor"
                wire:key="plant-photo-editor-{{ $photoKey }}">
                <div class="plant-photo-editor-image">
                    <a href="{{ asset("FDPfiles/splist/photo/{$photo['spcode']}/{$photo['filename']}") }}" target="_blank"
                        rel="noopener">
                        <img src="{{ asset("FDPfiles/splist/photo/{$photo['spcode']}/s_{$photo['filename']}") }}"
                            alt="{{ $photo['filename'] }}">
                    </a>
                    <span>{{ $photo['filename'] }}</span>
                </div>

                <div class="plant-photo-editor-fields">
                    <label>
                        類型
                        <select wire:model.defer="editingPhotos.{{ $photoKey }}.type">
                            <option value="">請選擇</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        種子雨分類(可留空)
                        <input type="text" list="plant-photo-type2-options"
                            wire:model.defer="editingPhotos.{{ $photoKey }}.type2">
                    </label>

                    <label>
                        fresh
                        <select wire:model.defer="editingPhotos.{{ $photoKey }}.fresh">
                            <option value="">請選擇</option>
                            @foreach ($freshOptions as $fresh)
                                <option value="{{ $fresh }}">{{ $fresh }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        status
                        <select wire:model.defer="editingPhotos.{{ $photoKey }}.status">
                            <option value="">請選擇</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        照片日期
                        <input type="date" wire:model.defer="editingPhotos.{{ $photoKey }}.photo_date">
                    </label>

                    <label>
                        公開與否
                        <select wire:model.defer="editingPhotos.{{ $photoKey }}.is_public">
                            <option value="1">公開</option>
                            <option value="0">不公開</option>
                        </select>
                    </label>

                    <label>
                        代表照片
                        <select wire:model.defer="editingPhotos.{{ $photoKey }}.is_featured">
                            <option value="0">一般照片</option>
                            <option value="1">首選代表照</option>
                        </select>
                    </label>

                    <label>
                        photo by
                        <input type="text" wire:model.defer="editingPhotos.{{ $photoKey }}.photoby">
                    </label>

                    <label class="plant-photo-editor-wide">
                        相片描述
                        <textarea rows="3" wire:model.defer="editingPhotos.{{ $photoKey }}.des"></textarea>
                    </label>

                    <p class="plant-photo-editor-actions">
                        <button type="submit" class="datasavebutton plant-photo-save-button">修改</button>
                        <button type="button" class="plant-photo-delete-button"
                            wire:click="deletePhoto('{{ $photoKey }}')"
                            onclick="return confirm('確定刪除這張照片？')">刪除</button>
                    </p>
                    @if (!empty($photoMessages[$photoKey]))
                        <p class="plant-photo-message plant-photo-editor-wide"
                            wire:key="photo-message-{{ $photoKey }}-{{ $messageVersion }}"
                            x-data="{ show: true }" x-show="show" x-transition.opacity
                            x-init="setTimeout(() => show = false, 3000)">{{ $photoMessages[$photoKey] }}</p>
                    @endif
                </div>
            </form>
        @empty
            <div class="plant-photo-empty">這個物種目前尚無照片。</div>
        @endforelse

        <div class="plant-photo-upload-card">
            <h6>新增照片</h6>
            <label class="plant-photo-dropzone">
                <input type="file" wire:model="newPhotoFile" accept="image/*">
                <span>
                    拖曳檔案至此，或點擊選擇檔案
                    <small>支援 JPG、PNG、WEBP，單檔最大 10MB</small>
                </span>
            </label>
            @error('newPhotoFile')
                <p class="plant-photo-form-error">{{ $message }}</p>
            @enderror
            <p class="plant-photo-form-error" wire:loading wire:target="newPhotoFile,uploadPhoto">處理照片中...</p>
        </div>
        @if ($photoUploadMessage)
            <p class="plant-photo-message" wire:key="photo-upload-message-{{ $messageVersion }}"
                x-data="{ show: true }" x-show="show" x-transition.opacity
                x-init="setTimeout(() => show = false, 3000)">{{ $photoUploadMessage }}</p>
        @endif
    </div>
</div>
