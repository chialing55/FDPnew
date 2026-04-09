@extends('layouts/app2')

@section('title', '編輯使用者-台灣森林動態樣區資料管理系統')

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('/css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
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
            <h2>編輯使用者：{{ $user->name }}（{{ $user->email }}）</h2>
        </div>

        <div class="user-edit-wrap">
            @if (session('status'))
                <div class="user-edit-alert user-edit-alert-success">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('temp_password'))
                <div class="user-edit-alert user-edit-alert-warning">
                    <div class="mb-1.5 font-bold">臨時密碼（只顯示一次）</div>
                    <div class="user-edit-inline-actions">
                        <code class="user-edit-code">
                            {{ session('temp_password') }}
                        </code>
                        <button type="button" class="user-edit-btn user-edit-btn-secondary"
                            onclick="copyTempPassword()">複製</button>
                    </div>
                    <div style="margin-top:6px; font-size:12px; opacity:.85;">
                        請將臨時密碼用內部方式提供給使用者，並請使用者登入後立即修改。
                    </div>
                </div>

                <script>
                    function copyTempPassword() {
                        const text = @json(session('temp_password'));
                        navigator.clipboard?.writeText(text);
                        alert('已複製臨時密碼');
                    }
                </script>
            @endif
            @if ($errors->any())
                <div class="user-edit-alert user-edit-alert-error">
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="text_box">

                <div class="user-edit-section-title">重設密碼</div>
                <p class='user-edit-section-hint' style="margin-bottom:16px;">重設一組臨時密碼給使用者，並會立刻讓舊密碼失效。</p>
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                    onsubmit="return confirm('確定要重設密碼？會立刻讓舊密碼失效。');">
                    @csrf
                    <button type="submit" class="user-edit-btn user-edit-btn-warning">
                        重設為臨時密碼
                    </button>
                </form>

            </div>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" style="width:100%; text-align:left;">
                @csrf

                <div class="text_box">
                    {{-- ===== 基本資料 ===== --}}
                    <div style="margin-bottom:2rem;">
                        <div class="user-edit-section-title">基本資料</div>
                        <div class="user-edit-basic-grid">
                            <div class="user-edit-basic-item">
                                <label class="user-edit-label">單位（unit）</label>
                                <input type="text" name="unit" value="{{ old('unit', $user->unit) }}"
                                    class="user-edit-basic-control">
                            </div>

                            <div class="user-edit-basic-item">
                                <label class="user-edit-label">角色（role）</label>
                                @php
                                    $roleOptions = ['admin' => '資料管理員', 'pi' => '計畫主持人', 'ra' => '研究助理'];
                                    $curRole = old('role', $user->role);
                                @endphp
                                <select name="role" class="user-edit-basic-control">
                                    @foreach ($roleOptions as $k => $label)
                                        <option value="{{ $k }}" {{ $curRole === $k ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="user-edit-basic-item">
                                <label class="user-edit-label">主要樣區（site_id）</label>
                                @php $curSite = old('site_id', $user->site_id); @endphp
                                <select name="site_id" class="user-edit-basic-control">
                                    <option value="">-</option>
                                    @foreach ($sites as $s)
                                        <option value="{{ $s->id }}"
                                            {{ (string) $curSite === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ===== 權限（user_scopes）===== --}}
                    @php
                        /**
                         * config/work.php 格式（你提供的）：
                         * [
                         *   ['key'=>'fushan.tree','title'=>'福山｜每木','site'=>'fushan','module'=>'tree', ...],
                         *   ...
                         * ]
                         *
                         * 這裡把它轉成：
                         * $workBySiteCode[site_code][module_key] = ['label'=>title,'key'=>key]
                         */
                        $workItems = config('work', []);
                        $workBySiteCode = [];
                        foreach ($workItems as $it) {
                            $siteCode = $it['site'] ?? null; // fushan
                            $moduleKey = $it['module'] ?? null; // tree / seeds / seedling
                            if (!$siteCode || !$moduleKey) {
                                continue;
                            }

                            $workBySiteCode[$siteCode][$moduleKey] = [
                                'label' => $it['title'] ?? ($it['key'] ?? $moduleKey),
                                'key' => $it['key'] ?? $moduleKey,
                            ];
                        }

                        // enabledMap：由 controller 傳入（以 module_key 為維度）
                        // 形狀：$enabledMap[site_id][module_key] = true
                        $enabledMap = $enabledMap ?? [];
                    @endphp

                    <div class="user-edit-section-title">樣區 × 工作項目權限</div>
                    <div class="user-edit-section-hint">
                        這裡會直接寫入 user_scopes（site_id + module_id），建議以「勾選」作為可使用模組的依據。
                    </div>

                    <div style="margin-top:16px;">
                        @foreach ($sites as $s)
                            @php
                                $siteCode = $s->code; // 你的 sites 表有 code（fushan/shoushan/...）
                                $modulesForThisSite = $workBySiteCode[$siteCode] ?? [];
                            @endphp

                            <div class="user-edit-subcard">
                                <div class="user-edit-site-title">{{ $s->name }}</div>

                                @if (empty($modulesForThisSite))
                                    <div style="padding:4px 0; font-size:14px; color:#64748b;">
                                        （此樣區尚未在 config/work.php 定義任何工作項目）
                                    </div>
                                @else
                                    <div class="user-edit-chip-row">
                                        @foreach ($modulesForThisSite as $moduleKey => $meta)
                                            @php
                                                $checked = !empty($enabledMap[$s->id][$moduleKey]);
                                            @endphp

                                            <label class="user-edit-chip">
                                                <input type="checkbox"
                                                    name="scopes[{{ $s->id }}][{{ $moduleKey }}]" value="1"
                                                    {{ $checked ? 'checked' : '' }}>
                                                <span>{{ $meta['label'] }}</span>
                                                <small>（{{ $moduleKey }}）</small>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="user-edit-actions">
                        <a href="{{ route('admin.users.index') }}" class="user-edit-btn user-edit-btn-secondary">
                            返回列表
                        </a>
                        <button type="submit" class="user-edit-btn user-edit-btn-primary">
                            儲存設定
                        </button>
                    </div>
                </div>


            </form>
        </div>
    </div>
@endsection
