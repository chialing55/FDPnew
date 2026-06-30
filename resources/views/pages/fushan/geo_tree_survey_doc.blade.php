@extends('layouts.geo-tree-survey')

@section('pagejs')
    <script>
        $('.list1').addClass('now');
        $('.list1 hr').css('color', '#91A21C');

        $('#download-record-paper').on('click', function () {
            const qx = $('#record-paper-qx').val();

            if (qx === '') {
                return;
            }

            const url = @json(route('admin.fushan.geo-tree-survey.record-paper', ['qx' => '__QX__']));
            window.open(url.replace('__QX__', qx), '_blank');
        });
    </script>
@endsection

@section('rightbox')
    <div class="flex text_outbox">
        <div class="text_box">
            <h2>下載紀錄紙</h2>
            <hr>
            <p>請選擇 qx；紀錄紙會包含該 qx 的所有 qy，並依 qx、qy 分頁。</p>

            <div style="margin: 20px 0;">
                請選擇輸出樣區：
                <select id="record-paper-qx" class="fs100" style="width: 70px;" name="qx">
                    <option value="">qx</option>
                    @for ($qx = 0; $qx < 25; $qx++)
                        <option value="{{ $qx }}">{{ $qx }}</option>
                    @endfor
                </select>

                <button id="download-record-paper" type="button" class="button1" style="margin-left: 12px;">
                    下載
                </button>
            </div>
        </div>
    </div>
@endsection
