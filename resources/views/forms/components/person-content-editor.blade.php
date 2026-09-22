@php($statePath = $getStatePath())
@php($initialState = $getState() ?? '')
@once
    <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
    <script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
@endonce
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: @entangle($statePath).defer,
            initialState: @js($initialState),
            editor: null,
            syncEditor() {
                if (! this.editor) return;
                const value = this.editor.value || '';
                this.state = value;
                this.$wire.set(@js($statePath), value, false);
            },
            initEditor() {
                const start = () => {
                    if (! window.Jodit || this.editor) return;
                    this.editor = window.Jodit.make(this.$refs.editor, {
                        height: 220,
                        minHeight: 120,
                        toolbarAdaptive: false,
                        toolbarSticky: true,
                        spellcheck: true,
                        beautifyHTML: true,
                        askBeforePasteHTML: false,
                        defaultActionOnPaste: 'insert_as_html',
                        placeholder: '請輸入內容…',
                        editorClassName: 'web-content',
                        iframe: true,
                        iframeCSSLinks: [@js(asset('css/web-content.css'))],
                        uploader: {
                            url: @js(route('cms.content-images.store')),
                            method: 'POST',
                            filesVariableName: () => 'images[]',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            isSuccess: response => response.success === true,
                            process: response => ({ files: response.data?.files || [], path: response.data?.path || '', baseurl: response.data?.baseurl || '', isImages: response.data?.isImages || [], error: response.data?.error || 0, msg: response.data?.messages || [] }),
                            error: error => window.alert(error.message || '圖片上傳失敗'),
                        },
                        buttons: ['source','|','undo','redo','|','paragraph','font','fontsize','brush','|','bold','italic','underline','strikethrough','|','ul','ol','outdent','indent','|','left','center','right','justify','|','link','image','table','hr','|','copyformat','eraser','fullsize'],
                        buttonsMD: ['source','undo','redo','paragraph','fontsize','bold','italic','underline','ul','ol','left','center','right','link','image','table','fullsize'],
                        buttonsSM: ['source','undo','redo','bold','italic','underline','ul','ol','link','image','table','fullsize'],
                    });
                    this.editor.value = this.initialState || this.state || '';
                    if (! this.state && this.initialState) this.state = this.initialState;
                    this.editor.events.on('change', value => {
                        this.state = value;
                        this.$wire.set(@js($statePath), value || '', false);
                    });
                    this.$watch('state', value => {
                        if (this.editor && value !== this.editor.value) this.editor.value = value || '';
                    });
                };
                const waitForJodit = () => window.Jodit ? start() : window.setTimeout(waitForJodit, 100);
                waitForJodit();
            }
        }"
        x-init="initEditor()"
        @submit.capture.window="syncEditor()"
        class="cms-jodit-editor"
    >
        <div wire:ignore><textarea x-ref="editor"></textarea></div>
    </div>
</x-dynamic-component>
