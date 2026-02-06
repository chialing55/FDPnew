@extends('layouts/app2')

@section('title', '分派權限-台灣森林動態樣區資料管理系統')

@section('css')
    <style>
        /* ===== 表單共用樣式（沿用 login 風格） ===== */
        .form-wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 20px 30px;
        }

        .form-card {
            background: rgba(255, 255, 255, .85);
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            padding: 18px;
        }

        .login-form {
            width: 100%;
            text-align: left;
        }

        .login-field {
            margin-bottom: 10px;
        }

        .login-label {
            display: block;
            color: #2f3e3b;
            font-size: 0.85em;
            margin-bottom: 6px;
        }

        .login-input {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #b6c2bf;
            border-radius: 6px;
            box-sizing: border-box;
            height: auto;
            background: rgba(255, 255, 255, .95);
        }

        .login-input:focus {
            outline: none;
            border-color: #4f766f;
            box-shadow: 0 0 0 2px rgba(79, 118, 111, 0.2);
        }

        .login-submit {
            padding: 10px 14px;
            background-color: #3f5f5b;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .login-submit:hover {
            background-color: #2f4f4a;
        }

        .btn-secondary {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, .15);
            background: rgba(255, 255, 255, .9);
            color: #2f3e3b;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
        }

        .grid-3 {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .grid-3>.col {
            flex: 1;
            min-width: 220px;
        }

        .section-title {
            font-weight: 700;
            color: #2f3e3b;
            margin: 10px 0 8px;
        }

        .section-hint {
            font-size: 12px;
            opacity: .75;
            margin-top: 6px;
        }

        .hr {
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, .08);
            margin: 14px 0;
        }

        .chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 10px;
            background: rgba(255, 255, 255, .9);
            cursor: pointer;
            user-select: none;
        }

        .chip input {
            transform: translateY(1px);
        }

        .chip small {
            opacity: .65;
            font-size: 12px;
        }

        .site-block {
            margin-bottom: 14px;
        }

        .site-name {
            font-weight: 700;
            opacity: .9;
            margin: 6px 0;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 18px;
        }
    </style>
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
            <h2>分派權限：{{ $user->name }}（{{ $user->email }}）</h2>

        </div>

        <div class="form-wrap">

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
            @if (session('temp_password'))
                <div
                    style="
        margin-bottom: 14px;
        padding: 10px 12px;
        border: 1px solid rgba(234,179,8,.45);
        background: rgba(234,179,8,.14);
        color: #713f12;
        border-radius: 6px;
    ">
                    <div style="font-weight:700; margin-bottom:6px;">臨時密碼（只顯示一次）</div>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <code
                            style="font-size:16px; padding:4px 8px; border-radius:6px; background:rgba(255,255,255,.9); border:1px solid rgba(0,0,0,.12);">
                            {{ session('temp_password') }}
                        </code>
                        <button type="button" class="btn-secondary" onclick="copyTempPassword()">複製</button>
                    </div>
                    <div style="font-size:12px; opacity:.85; margin-top:6px;">
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
                <div
                    style="
                margin-bottom: 14px;
                padding: 10px 12px;
                border: 1px solid rgba(220,38,38,.25);
                background: rgba(220,38,38,.08);
                color: #7f1d1d;
                border-radius: 6px;
            ">
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif
            <div class="form-card">
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                    onsubmit="return confirm('確定要重設密碼？會立刻讓舊密碼失效。');" style="margin-bottom: 14px;">
                    @csrf
                    <button type="submit" class="login-submit" style="background:#b45309;">
                        重設為臨時密碼
                    </button>
                </form>
            </div>

            <div class="form-card">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="login-form">
                    @csrf

                    {{-- ===== 基本資料 ===== --}}
                    <div class="grid-3">
                        <div class="col login-field">
                            <label class="login-label">單位（unit）</label>
                            <input type="text" name="unit" value="{{ old('unit', $user->unit) }}" class="login-input">
                        </div>

                        <div class="col login-field">
                            <label class="login-label">角色（role）</label>
                            @php
                                $roleOptions = ['admin' => '資料管理員', 'pi' => '計畫主持人', 'ra' => '研究助理'];
                                $curRole = old('role', $user->role);
                            @endphp
                            <select name="role" class="login-input">
                                @foreach ($roleOptions as $k => $label)
                                    <option value="{{ $k }}" {{ $curRole === $k ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col login-field">
                            <label class="login-label">主要樣區（site_id）</label>
                            @php $curSite = old('site_id', $user->site_id); @endphp
                            <select name="site_id" class="login-input">
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

                    <div class="hr"></div>

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

                    <div class="section-title">樣區 × 工作項目權限</div>
                    <div class="section-hint">
                        這裡會直接寫入 user_scopes（site_id + module_id），建議以「勾選」作為可使用模組的依據。
                    </div>

                    <div style="margin-top: 10px;">
                        @foreach ($sites as $s)
                            @php
                                $siteCode = $s->code; // 你的 sites 表有 code（fushan/shoushan/...）
                                $modulesForThisSite = $workBySiteCode[$siteCode] ?? [];
                            @endphp

                            <div class="site-block">
                                <div class="site-name">{{ $s->name }}</div>

                                @if (empty($modulesForThisSite))
                                    <div style="opacity:.75; font-size:13px; padding: 4px 0;">
                                        （此樣區尚未在 config/work.php 定義任何工作項目）
                                    </div>
                                @else
                                    <div class="chips">
                                        @foreach ($modulesForThisSite as $moduleKey => $meta)
                                            @php
                                                $checked = !empty($enabledMap[$s->id][$moduleKey]);
                                            @endphp

                                            <label class="chip">
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

                    <div class="actions">
                        <a href="{{ route('admin.users.index') }}"
                            style="
                                        display:inline-block;
                                        padding:6px 12px;
                                        border-radius:6px;
                                        border:1px solid rgba(0,0,0,.15);
                                        background: rgba(255,255,255,.9);
                                        color:#2f3e3b;
                                        text-decoration:none;">返回列表</a>
                        <button type="submit" class="login-submit">儲存設定</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
