@extends('layouts/app2')

@section('title', '使用者管理-台灣森林動態樣區資料管理系統')

@section('content')
    <div class="icon">
        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header')

    <div class="content">
        <div class="header_bottom fc-w flex"
            style="padding: 10px 30px;  align-items: center;
  justify-content: space-between">
            <h2>使用者管理</h2>

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

            {{-- 卡片容器 --}}
            <div
                style="
            background: rgba(255,255,255,.85);
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 10px;
            overflow: hidden;
        ">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse; min-width: 980px;">
                        <thead>
                            <tr style="background: rgba(0,0,0,.04);">
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    帳號</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    Email</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">姓名
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">單位
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">角色
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    主要樣區</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">狀態
                                </th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    註冊時間</th>
                                <th style="padding: 12px 10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.08);">
                                    權限
                                </th>
                                <th style="padding: 12px 10px; text-align:center; border-bottom:1px solid rgba(0,0,0,.08);">
                                    操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $u)
                                @php
                                    $roleLabel = match ($u->role) {
                                        'admin' => '資料管理員',
                                        'pi' => '計畫主持人',
                                        'ra' => '研究助理',
                                        default => '未知',
                                    };

                                    $isDataAdmin = $u->role === 'admin';

                                    $statusLabel = match ($u->status) {
                                        'approved' => '已啟用',
                                        'pending' => '',
                                        'rejected' => '已拒絕',
                                        default => $u->status ?? '-',
                                    };
                                @endphp

                                <tr>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">{{ $u->account }}
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">{{ $u->email }}
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">{{ $u->name }}
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">{{ $u->unit ?? '-' }}
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        @if ($isDataAdmin)
                                            <span
                                                style="display:inline-block; padding:2px 8px; border-radius:999px; background:rgba(245, 158, 11, .18); border:1px solid rgba(245, 158, 11, .35); color:#92400e; font-weight:700;">
                                                {{ $roleLabel }}
                                            </span>
                                        @else
                                            {{ $roleLabel }}
                                        @endif
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $u->site?->name ?? '-' }}
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $statusLabel }}

                                        @if ($u->status === 'pending')
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
                                        @endif
                                    </td>
                                    <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">
                                        {{ $u->created_at }}</td>
<td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
    @php
        // 依 site_id 分組，然後每組列出 module 名稱
        $groups = $u->userScopes
            ->groupBy('site_id')
            ->map(function ($rows) {
                $siteName = optional($rows->first()->site)->name ?? '（未知樣區）';
                $moduleNames = $rows->pluck('module.name')->filter()->unique()->values();
                return [
                    'site' => $siteName,
                    'modules' => $moduleNames,
                ];
            })
            ->values();
    @endphp

    @if (! $u->canAccessFilament() && $groups->isEmpty())
        <span style="opacity:.6;">-</span>
    @else
        <div style="display:flex; flex-direction:column; gap:6px;">
            @if ($u->canAccessFilament())
                <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                    <span style="font-weight:700; font-size:12px; color:#2f3e3b;">
                        後台：
                    </span>
                    <span style="
                        display:inline-block;
                        padding:2px 8px;
                        border-radius:999px;
                        border:1px solid rgba(245, 158, 11, .35);
                        background: rgba(245, 158, 11, .18);
                        font-size: 12px;
                        color:#92400e;
                        font-weight:700;
                    ">網頁後端管理平台</span>
                </div>
            @endif

            @foreach ($groups as $g)
                <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                    <span style="font-weight:700; font-size:12px; color:#2f3e3b;">
                        {{ $g['site'] }}：
                    </span>

                    @forelse ($g['modules'] as $m)
                        <span style="
                            display:inline-block;
                            padding:2px 8px;
                            border-radius:999px;
                            border:1px solid rgba(0,0,0,.12);
                            background: rgba(255,255,255,.9);
                            font-size: 12px;
                            color:#2f3e3b;
                        ">{{ $m }}</span>
                    @empty
                        <span style="opacity:.6; font-size:12px;">（無模組）</span>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif
</td>


                                    <td
                                        style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:center; white-space:nowrap;">
                                        <a href="{{ route('admin.users.edit', $u) }}"
                                            style="
                                        display:inline-block;
                                        padding:6px 12px;
                                        border-radius:6px;
                                        border:1px solid rgba(0,0,0,.15);
                                        background: rgba(255,255,255,.9);
                                        color:#2f3e3b;
                                        text-decoration:none;
                                    ">編輯</a>
                                        @if (!$u->is_admin)
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                style="display:inline-block; margin-left:8px;"
                                                onsubmit="return confirm('確定要刪除使用者：{{ $u->name }}（{{ $u->account }}）嗎？\n此操作會同時刪除其權限範圍（user_scope）。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    style="
                    padding:6px 12px;
                    border-radius:6px;
                    border:1px solid rgba(220,38,38,.45);
                    background: rgba(220,38,38,.08);
                    color:#991b1b;
                    cursor:pointer;
                ">
                                                    刪除
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="padding: 18px; text-align:center; opacity:.8;">
                                        目前沒有使用者
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
