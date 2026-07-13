@php($statePath = $getStatePath())
@php($initialState = $getState() ?? '')
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: @entangle($statePath).defer,
            initialState: @js($initialState),
            editor: null,
            previewOpen: false,
            openPreview() {
                if (this.editor) this.state = this.editor.value;
                this.previewOpen = true;
            },
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
                        height: 420,
                        minHeight: 300,
                        toolbarAdaptive: false,
                        toolbarSticky: true,
                        spellcheck: true,
                        beautifyHTML: true,
                        askBeforePasteHTML: false,
                        defaultActionOnPaste: 'insert_as_html',
                        placeholder: '請輸入內容…',
                        editorClassName: 'web-content',
                        iframe: true,
                        iframeCSSLinks: [
                            @js(asset('css/web-content.css')),
                        ],
                        uploader: {
                            url: @js(route('cms.content-images.store')),
                            method: 'POST',
                            filesVariableName: () => 'images[]',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            isSuccess: response => response.success === true,
                            process: response => ({
                                files: response.data?.files || [],
                                path: response.data?.path || '',
                                baseurl: response.data?.baseurl || '',
                                isImages: response.data?.isImages || [],
                                error: response.data?.error || 0,
                                msg: response.data?.messages || [],
                            }),
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
                if (window.Jodit) start(); else window.addEventListener('load', start, { once: true });
            }
        }"
        x-init="initEditor()"
        @submit.capture.window="syncEditor()"
        class="cms-jodit-editor"
    >
        <details class="cms-content-class-help">
            <summary>可用排版 class 與 HTML 範例</summary>
            <div class="cms-content-class-help-body">
                <dl>
                    <div><dt><code>page-two-column</code></dt><dd>響應式雙欄排版；小螢幕為單欄，桌面寬度為兩欄。</dd></div>
                    <div><dt><code>web-content</code></dt><dd>由系統自動套用，不需要寫進內容 HTML。</dd></div>
                    <div><dt><code>figure / figcaption</code></dt><dd>圖片與圖說的標準 HTML 標籤，會自動置中並套用圖說樣式。</dd></div>
                </dl>
                <pre><code>&lt;div class="page-two-column"&gt;
    &lt;figure&gt;
        &lt;img src="/storage/圖片路徑.jpg" alt="圖片說明"&gt;
        &lt;figcaption&gt;第一張圖片圖說&lt;/figcaption&gt;
    &lt;/figure&gt;
    &lt;figure&gt;
        &lt;img src="/storage/圖片路徑.jpg" alt="圖片說明"&gt;
        &lt;figcaption&gt;第二張圖片圖說&lt;/figcaption&gt;
    &lt;/figure&gt;
&lt;/div&gt;</code></pre>
                <p>請在編輯器的「原始碼」模式貼入 HTML；一般標題、段落、清單、表格與引用不需額外 class。</p>
            </div>
        </details>
        <div class="mb-3 flex justify-end">
            <button type="button" class="cms-preview-button" @click.stop="openPreview()">預覽</button>
        </div>
        <div wire:ignore><textarea x-ref="editor"></textarea></div>

        <template x-teleport="body">
            <div x-show="previewOpen" x-cloak class="cms-preview-overlay" @keydown.escape.window="previewOpen = false">
                <div class="cms-preview-dialog">
                    <div class="cms-preview-header"><strong>內容預覽</strong><button type="button" @click="previewOpen = false">關閉</button></div>
                    <div class="cms-preview-body">
                        <div x-show="state" class="web-content prose prose-sm max-w-none" x-html="state"></div>
                        <p x-show="! state" class="cms-empty-preview">尚無內容</p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
