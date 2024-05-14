<div>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
    <div class='text_box'>    
        <h2>新增資料</h2>
<hr>
    <div>
        <h6>已完成資料比對、上傳檔案、匯入大表的樣線</h2>
      
        @include('includes.fstree-census5-progress')
    </div>
        <div style='margin:20px 0; display: flex;align-items: flex-end;'>
            <ul style='margin:0 20px 0 0 '>
                <li>加入新增資料進 census 5 大表，如為漏資料，請至 後端資料更正。</li>
                <li>只能新增已匯入 census 5 大表的樣線資料。</li>
            </ul>
          
            <button wire:click.prevent="addData()" style="margin-left: 20px;" >GO</button>

        </div>
    @if($show=='ok')

    <hr>
    @include('includes.str-recruit-entrytable')
    @endif
    </div>
</div>
