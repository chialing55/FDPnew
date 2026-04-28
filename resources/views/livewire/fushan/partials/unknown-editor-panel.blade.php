@if($editorOpen)
    <div id="unknown-editor-panel" class="unknown-editor-panel text_box">
        <div class="unknown-editor-header">
            <div>
                <h6>{{ $editorMode === 'create' ? '新增 UNKNOWN' : '編輯 ' . $editingUnkName }}</h6>
                @if($editorMode === 'edit')
                    <span>物種描述與照片描述</span>
                @endif
            </div>
            <button type="button" class="unknown-editor-close" wire:click="closeEditor" title="關閉編輯區">X</button>
        </div>

        @if($editorMessage)
            <p class="unknown-editor-message app-feedback-note app-feedback-note--success">{{ $editorMessage }}</p>
        @endif

        @if($editorMode === 'create')
            <form wire:submit.prevent="createUnknown" class="unknown-editor-section unknown-description-form">
                <label>
                    <span>UNKNOWN 編號</span>
                    <input type="text" class="fs100 unknown-description-input" wire:model.defer="newUnkName">
                </label>
                @error('newUnkName') <p class="unknown-form-error">{{ $message }}</p> @enderror

                <label>
                    <span>物種描述</span>
                    <input type="text" class="fs100 unknown-description-input" wire:model.defer="newUnkDes">
                </label>
                @error('newUnkDes') <p class="unknown-form-error">{{ $message }}</p> @enderror

                <p class="unknown-editor-actions">
                    <button type="submit" class="datasavebutton unknown-normal-button">建立</button>
                </p>
            </form>
        @else
            <form wire:submit.prevent="saveUnknownDescription" class="unknown-editor-section unknown-description-form">
                <label>
                    <span>物種描述</span>
                    <input type="text" class="fs100 unknown-description-input" wire:model.defer="editingUnkDes">
                </label>
                @error('editingUnkDes') <p class="unknown-form-error">{{ $message }}</p> @enderror

                <p class="unknown-editor-actions">
                    <button type="submit" class="datasavebutton unknown-normal-button">儲存物種描述</button>
                </p>
            </form>

            <div class="unknown-editor-section">
                <h6>照片描述</h6>
                <p class="unknown-optional-note">* 以下資訊皆可留白</p>
                @forelse($editingPhotos as $photoId => $photo)
                    <form wire:submit.prevent="savePhoto({{ $photoId }})" class="unknown-photo-editor" wire:key="unknown-photo-editor-{{ $photoId }}">
                        <div class="unknown-photo-editor-image">
                            <img src="{{ asset("FDPfiles/splist/photo/unknown/{$editingUnkName}/s_{$photo['filename']}") }}" alt="{{ $photo['filename'] }}">
                            <span>{{ $photo['filename'] }}</span>
                        </div>

                        <div class="unknown-photo-editor-fields">
                            <label>
                                類型
                                <select class="fs100" wire:model.defer="editingPhotos.{{ $photoId }}.code">
                                    @foreach($codelist as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                調查時間
                                <input type="date" class="fs100" wire:model.defer="editingPhotos.{{ $photoId }}.censustime">
                            </label>

                            <label>
                                photo by
                                <input type="text" class="fs100" wire:model.defer="editingPhotos.{{ $photoId }}.photoby">
                            </label>

                            <label class="unknown-photo-description">
                                相片描述
                                <textarea class="fs100" rows="3" placeholder="補充說明" wire:model.defer="editingPhotos.{{ $photoId }}.des"></textarea>
                            </label>

                            <p class="unknown-editor-actions unknown-photo-description">
                                <button type="submit" class="datasavebutton unknown-normal-button">修改</button>
                                <button type="button" class="unknown-delete-button" wire:click="deletePhoto({{ $photoId }})" onclick="return confirm('確定刪除這張照片？')">刪除</button>
                            </p>
                        </div>
                    </form>
                @empty
                    <p class="unknown-empty-note">尚無照片</p>
                @endforelse
            </div>

            <div class="unknown-editor-section unknown-upload-card">
                <h6>新增照片</h6>
                <label class="unknown-dropzone">
                    <input type="file" wire:model="newPhotoFile" accept="image/*">
                    <span>拖曳檔案至此，或點擊選擇檔案</span>
                    <small>支援 JPG、PNG、WEBP，單檔最大 10MB</small>
                </label>
                @error('newPhotoFile') <p class="unknown-form-error">{{ $message }}</p> @enderror
                <p class="unknown-upload-status" wire:loading wire:target="newPhotoFile,uploadPhoto">處理照片中...</p>
            </div>
        @endif
    </div>
@endif
