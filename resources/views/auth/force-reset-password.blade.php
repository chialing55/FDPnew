@extends('layouts/app2')
@section('title', '更換密碼-台灣森林動態樣區資料管理系統')
@section('css')
    <style>
        /* ===== Force reset password page ===== */
        .card {
            max-width: 520px;
            margin: 28px auto;
            padding: 0 16px;
        }

        .card__box {
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            padding: 18px 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .card__h1 {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .2px;
        }

        .card__desc {
            margin: 0 0 16px;
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .form-row {
            margin-bottom: 12px;
        }

        .label {
            display: block;
            color: #2f3e3b;
            font-size: 0.85em;
            margin-bottom: 6px;
        }

        .input {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #b6c2bf;
            border-radius: 6px;
            box-sizing: border-box;
            height: auto;
            background: rgba(255, 255, 255, .95);
        }

        .input:focus {
            outline: none;
            border-color: #4f766f;
            box-shadow: 0 0 0 2px rgba(79, 118, 111, 0.2);
        }

        .help-error {
            margin-top: 6px;
            font-size: 12px;
            color: #b00020;
        }

        .alert-success {
            margin: 0 0 12px;
            padding: 10px 12px;
            border: 1px solid #bfe6c8;
            background: #ecfff1;
            color: #1b6b34;
            border-radius: 10px;
            font-size: 13px;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 35px;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #4f766f;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .password-toggle .eye-closed {
            display: none;
        }
    </style>
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
{{-- Nzv0gHZehj --}}
@section('content')
    <div class="icon">
        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header')
    <div class="content">
        <div class="header_bottom fc-w flex"
            style="padding: 10px 30px;  align-items: center;
  justify-content: space-between">
            <h2>更換密碼：{{ $user->name }}（{{ $user->email }}）</h2>

            <span style='margin-left:20px' class='back'>重新選擇工作項目</span>
        </div>
        <div class="card">
            <div class="card__box">

                <p class="card__desc">
                    為確保帳號安全，請先使用
                    {{ $user->force_password_reset ? '臨時密碼' : '原密碼' }}
                    驗證後設定新密碼。
                </p>


                @if (session('status'))
                    <div class="alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.password.force.update') }}">
                    @csrf

                    <div class="form-row password-field">
                        <label class="label">{{ $user->force_password_reset ? '臨時密碼' : '原密碼' }}</label>
                        <input id="current_password" class="input" type="password" name="current_password" required>

                        <button type="button" class="password-toggle" onclick="togglePasswordEye('current_password', this)"
                            aria-label="顯示密碼">

                            <!-- eye -->
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                        c4.478 0 8.268 2.943 9.542 7
                                                        -1.274 4.057-5.064 7-9.542 7
                                                        -4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            <!-- eye-off -->
                            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                                        c-1.274 4.057-5.064 7-9.542 7
                                                        a9.96 9.96 0 01-4.293-.926" />
                            </svg>
                        </button>
                        @error('current_password')
                            <div class="help-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row password-field">
                        <label class="label">新密碼</label>
                        <input id="password" class="input" type="password" name="password" required>

                        <button type="button" class="password-toggle" onclick="togglePasswordEye('password', this)"
                            aria-label="顯示密碼">

                            <!-- eye -->
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                        c4.478 0 8.268 2.943 9.542 7
                                                        -1.274 4.057-5.064 7-9.542 7
                                                        -4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            <!-- eye-off -->
                            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                                        c-1.274 4.057-5.064 7-9.542 7
                                                        a9.96 9.96 0 01-4.293-.926" />
                            </svg>
                        </button>
                        @error('password')
                            <div class="help-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row password-field">
                        <label class="label">確認新密碼</label>
                        <input class="input" id="password_confirmation" type="password" name="password_confirmation"
                            required>

                        <button type="button" class="password-toggle"
                            onclick="togglePasswordEye('password_confirmation', this)" aria-label="顯示密碼">

                            <!-- eye -->
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                        c4.478 0 8.268 2.943 9.542 7
                                                        -1.274 4.057-5.064 7-9.542 7
                                                        -4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            <!-- eye-off -->
                            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.24 7.76A9.956 9.956 0 0121.542 12
                                                        c-1.274 4.057-5.064 7-9.542 7
                                                        a9.96 9.96 0 01-4.293-.926" />
                            </svg>
                        </button>
                    </div>

                    <button class="btn btn--primary" type="submit" style="width:100%; padding:12px 14px;">
                        更新密碼
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
