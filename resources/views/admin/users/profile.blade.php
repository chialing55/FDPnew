@extends('layouts/app2')

@section('title', '個人資訊-台灣森林動態樣區資料管理系統')

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('/css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
@endsection

@section('js')
    <script>
        function togglePasswordEye(inputId, button) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const eyeOpen = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
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
            <h2>個人資訊：{{ $user->name }}（{{ $user->email }}）</h2>
            <span style='margin-left:20px' class='back'>重新選擇工作項目</span>
        </div>

        <div class="user-edit-wrap">
            @if (session('status'))
                <div class="user-edit-alert user-edit-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="user-edit-alert user-edit-alert-error">
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.update') }}" style="width:100%; text-align:left;">
                @csrf

                <div class="text_box">
                    <div class="user-edit-section-title">基本資料</div>
                    <div class="user-edit-basic-grid">
                        <div class="user-edit-basic-item">
                            <label class="user-edit-label">單位（unit）</label>
                            <input type="text" name="unit" value="{{ old('unit', $user->unit) }}"
                                class="user-edit-basic-control">
                        </div>

                        <div class="user-edit-basic-item">
                            <label class="user-edit-label">角色（role）</label>
                            @if ($user->role === 'admin')
                                <input type="text" value="資料管理員" class="user-edit-basic-control" disabled>
                            @else
                                @php
                                    $roleOptions = ['pi' => '計畫主持人', 'ra' => '研究助理'];
                                    $curRole = old('role', $user->role);
                                @endphp
                                <select name="role" class="user-edit-basic-control">
                                    @foreach ($roleOptions as $k => $label)
                                        <option value="{{ $k }}" {{ $curRole === $k ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
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

                    <div class="user-edit-section-title" style="margin-top:2rem;">修改密碼</div>
                    <div class="user-edit-section-hint" style="margin-bottom:16px;">
                        若不需要修改密碼，可將以下欄位留空。<br>
                        密碼至少 8 碼，需含英文字母、數字與符號。
                    </div>

                    <div class="user-edit-basic-grid">
                        <div class="user-edit-basic-item user-edit-password-field">
                            <label class="user-edit-label">目前密碼</label>
                            <input id="profile_current_password" type="password" name="current_password"
                                class="user-edit-basic-control"
                                placeholder="請輸入目前密碼">
                            <button type="button" class="user-edit-password-toggle"
                                onclick="togglePasswordEye('profile_current_password', this)" aria-label="顯示密碼">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5
                                        c4.478 0 8.268 2.943 9.542 7
                                        -1.274 4.057-5.064 7-9.542 7
                                        -4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                        c-1.274 4.057-5.064 7-9.542 7
                                        a9.96 9.96 0 01-4.293-.926" />
                                </svg>
                            </button>
                        </div>

                        <div class="user-edit-basic-item user-edit-password-field">
                            <label class="user-edit-label">新密碼</label>
                            <input id="profile_password" type="password" name="password"
                                class="user-edit-basic-control"
                                placeholder="請輸入新密碼">
                            <button type="button" class="user-edit-password-toggle"
                                onclick="togglePasswordEye('profile_password', this)" aria-label="顯示密碼">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5
                                        c4.478 0 8.268 2.943 9.542 7
                                        -1.274 4.057-5.064 7-9.542 7
                                        -4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                        c-1.274 4.057-5.064 7-9.542 7
                                        a9.96 9.96 0 01-4.293-.926" />
                                </svg>
                            </button>
                        </div>

                        <div class="user-edit-basic-item user-edit-password-field">
                            <label class="user-edit-label">確認新密碼</label>
                            <input id="profile_password_confirmation" type="password" name="password_confirmation"
                                class="user-edit-basic-control"
                                placeholder="請再次輸入新密碼">
                            <button type="button" class="user-edit-password-toggle"
                                onclick="togglePasswordEye('profile_password_confirmation', this)" aria-label="顯示密碼">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5
                                        c4.478 0 8.268 2.943 9.542 7
                                        -1.274 4.057-5.064 7-9.542 7
                                        -4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                        c-1.274 4.057-5.064 7-9.542 7
                                        a9.96 9.96 0 01-4.293-.926" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="user-edit-actions">
                        <button type="submit" class="user-edit-btn user-edit-btn-primary">
                            儲存個人資訊
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
