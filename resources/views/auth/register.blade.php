@extends('layouts/adminapp')

@section('title', '註冊-台灣森林動態樣區資料管理系統')

@section('js')
    <script>
        $('.icon').hide();

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

@section('css')
    <style>
        .login-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: min(450px, 92vw);
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
            font-size: 0.8em;
            padding-bottom: 3px;
        }

        .login-input {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #b6c2bf;
            border-radius: 4px;
            box-sizing: border-box;
            height: auto;
        }

        .login-input:focus {
            outline: none;
            border-color: #4f766f;
            box-shadow: 0 0 0 2px rgba(79, 118, 111, 0.2);
        }

        .login-submit {
            width: 100%;
            padding: 10px 0;
            background-color: #3f5f5b;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        .login-submit:hover {
            background-color: #2f4f4a;
        }

        .login-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 14px;
        }

        .login-register {
            color: #4a6b66;
            text-decoration: none;
        }

        .login-register:hover {
            text-decoration: underline;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 32px;
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

        .login-input::placeholder {
            font-size: 12px;
            /*        color: #9aa6a3;*/
            /* 可選：讓提示字淡一點 */
        }
    </style>
@endsection

@section('content')
    <div class="index">
        <div class="indexbox">
            <h1>台灣森林動態樣區資料管理系統</h1>
            <h2 style='color:#4f766f;'>建立新帳號</h2>
            <div id="inner" style="padding-top:0; padding-bottom:30px; line-height:1em;">
                <div class="login-wrap">
                    <div class="login-card">

                        {{-- 錯誤訊息 --}}
                        @if ($errors->any())
                            <div class="mb-4 p-3 text-red-700">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="login-form">
                            @csrf

                            <div class="login-field">
                                <label class="login-label">姓名</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="login-input">
                            </div>

                            <div class="login-field">
                                <label class="login-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="login-input">
                            </div>
                            <div class="login-field">
                                <label class="login-label">單位</label>
                                <input type="text" name="unit" value="" required class="login-input">
                            </div>

                            <div class="login-field">
                                <label class="login-label">職稱</label>
                                <select name="role" required class="login-input">
                                    <option value="">請選擇職稱</option>
                                    <option value="資料管理員">資料管理員</option>
                                    <option value="計畫主持人">計畫主持人</option>
                                    <option value="研究助理">研究助理</option>
                                </select>
                            </div>
                            <div class="login-field">
                                <label class="login-label">研究樣區</label>
                                <select name="site_id" required class="login-input">
                                    <option value="">請選擇樣區</option>

                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"
                                            {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="login-field password-field">
                                <label class="login-label">密碼</label>
                                <input type="password" name="password" id="password" required class="login-input"
                                    placeholder="至少8個字元，包含英文字母、數字和符號">

                                <button type="button" class="password-toggle"
                                    onclick="togglePasswordEye('password', this)">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                                                        c4.478 0 8.268 2.943 9.542 7
                                                                                        -1.274 4.057-5.064 7-9.542 7
                                                                                        -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>

                            <div class="login-field password-field">
                                <label class="login-label">確認密碼</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="login-input">

                                <button type="button" class="password-toggle"
                                    onclick="togglePasswordEye('password_confirmation', this)">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                                                        c4.478 0 8.268 2.943 9.542 7
                                                                                        -1.274 4.057-5.064 7-9.542 7
                                                                                        -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>

                            <button type="submit" class="login-submit">
                                註冊
                            </button>

                            <div class="login-footer">
                                已有帳號？
                                <a href="{{ route('login') }}" class="login-register">返回登入</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
