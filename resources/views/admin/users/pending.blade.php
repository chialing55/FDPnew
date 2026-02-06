{{-- resources/views/admin/users/pending.blade.php --}}
@extends('layouts/app2')

@section('title', '待審使用者-台灣森林動態樣區資料管理系統')

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 需要的話可加上互動
        });
    </script>
@endsection

@section('content')

    <div class="icon">
        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header')

    <div class="content">

        <div class="header_bottom fc-w flex"
            style="padding: 10px 30px;  align-items: center;
  justify-content: space-between">
            <h2>待審使用者</h2>

            <span style='margin-left:20px' class='back'>重新選擇工作項目</span>
        </div>

        <div style="padding: 20px 30px;">

            @if (session('status'))
                <div
                    style="
                margin-bottom: 14px;
                padding: 10px 12px;
                border: 1px solid rgba(34,197,94,.35);
                background: rgba(34,197,94,.12);
                color: #14532d;
                border-radius: 6px;
            ">
                    {{ session('status') }}
                </div>
            @endif

            <div
                style="
            background: rgba(255,255,255,.85);
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 10px;
            overflow: hidden;
        ">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse; min-width: 720px;">
                        <thead>
                            <tr style="background: rgba(0,0,0,.04);">
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    Email</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">姓名
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    註冊時間</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">單位
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">職稱
                                </th>
                                <th style="padding: 12px 10px; text-align:center; border-bottom:1px solid rgba(0,0,0,.08);">
                                    操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $u->email }}
                                    </td>
                                    <td style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06);">
                                        {{ $u->name }}
                                    </td>
                                    <td style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $u->created_at }}
                                    </td>
                                    <td style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $u->unit }}
                                    </td>
                                    <td style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $u->role_label }}
                                    </td>

                                    <td
                                        style="padding: 10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:center; white-space:nowrap;">
                                        <form method="POST" action="{{ route('admin.users.approve', $u) }}"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit"
                                                style="
                                            padding: 6px 12px;
                                            border: 0;
                                            border-radius: 6px;
                                            background: #2f7a5f;
                                            color: #fff;
                                            cursor: pointer;
                                        ">通過</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.reject', $u) }}"
                                            style="display:inline; margin-left:6px;">
                                            @csrf
                                            <button type="submit" onclick="return confirm('確定要拒絕？')"
                                                style="
                                                padding: 6px 12px;
                                                border: 0;
                                                border-radius: 6px;
                                                background: #b23b3b;
                                                color: #fff;
                                                cursor: pointer;
                                            ">拒絕</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 18px; text-align:center; opacity:.8;">
                                        目前沒有待審帳號
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top: 14px; opacity:.8; font-size: 13px;">
                提示：通過後使用者即可登入；若需限制權限，請至<a href="{{ route('admin.users.index') }}"
                    class="font-normal hover:font-bold hover:text-white">使用者管理介面</a>設定角色/樣區。
            </div>

        </div>
    </div>
@endsection
