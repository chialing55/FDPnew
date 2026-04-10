@extends('layouts/adminapp')

@section('title', '登入-台灣森林動態樣區資料管理系統')

@section('header_js')
@endsection

@section('js')
    <script type="text/javascript">
        $('.icon').hide();
        // $("footer").hide();
    </script>

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

        /* ===== 登入表單 ===== */
        .login-form {
            width: 100%;
            text-align: left;

        }

        /* 每個欄位 */
        .login-field {
            margin-bottom: 8px;
        }

        .login-label {
            display: block;
            color: #2f3e3b;
            font-size: 0.8em;
            /* 深森林灰 */
        }

        /* 輸入框 */
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
            /* 福山綠 */
            box-shadow: 0 0 0 2px rgba(79, 118, 111, 0.2);
        }

        /* 記住我 + 忘記密碼 */
        .login-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0 16px;
            font-size: 13px;
            color: #555;
        }

        .login-remember {
            cursor: pointer;
        }

        .login-remember input {
            margin-right: 4px;
        }


        /* 登入按鈕 */
        .login-submit {
            width: 100%;
            padding: 10px 0;
            background-color: #3f5f5b;
            /* 福山系統常用深綠 */
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        .login-submit:hover {
            background-color: #2f4f4a;
        }

        /* 註冊連結區 */
        .login-footer {
            margin-top: 10px;
            text-align: center;
            font-size: 14px;
        }

        .login-register {
            color: #4a6b66;
            /* 偏福山綠 */
            text-decoration: none;
        }

        .login-register:hover {
            text-decoration: underline;
            color: #4a6b66;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50px;
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

@section('content')
    <div class='index'>
        <div class='indexbox'>
            <p>台灣森林動態樣區資料管理系統</p>

            <div id="inner" style='padding-top:0px; padding-bottom:30px;'>
                <div class="login-wrap">
                    <div class="login-card">
                        {{-- Breeze / Laravel Auth 狀態訊息（例如重設密碼成功） --}}
                        {{-- 狀態訊息 --}}
                        @if (session('logout_success'))
                            <div class="mb-4 font-medium text-green-600">
                                {{ session('logout_success') }}
                            </div>
                        @endif
                        @if (session('status'))
                            <div class="mb-4 p-3 text-green-700">
                                {{ session('status') }}
                            </div>
                        @endif
                        @error('account')
                            <div class="mb-4 p-3 text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        <form method="POST" action="{{ route('login') }}" class="login-form">
                            @csrf

                            <div class="login-field">
                                <label class="login-label">帳號</label>
                                <input type="text" name="account" value="{{ old('account') }}" required
                                    class="login-input">
                            </div>

                            <div class="login-field password-field">
                                <label class="login-label">密碼</label>

                                <input type="password" name="password" id="password" required class="login-input">

                                <button type="button" class="password-toggle" onclick="togglePasswordEye('password', this)"
                                    aria-label="顯示密碼">

                                    <!-- eye -->
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.584 10.586A2 2 0 0012 14a2 2 0 001.414-.586" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.24 7.76A9.956 9.956 0 0121.542 12
                        c-1.274 4.057-5.064 7-9.542 7
                        a9.96 9.96 0 01-4.293-.926" />
                                    </svg>
                                </button>
                            </div>


                            <div class="login-row">
                                {{-- <label class="login-remember">
                                    <input type="checkbox" name="remember">
                                    記住我
                                </label> --}}

                                <span>
                                    忘記密碼請洽管理員
                                </span>
                            </div>

                            <button type="submit" class="login-submit">
                                登入
                            </button>
                            <div class="login-footer">
                                <a href="{{ route('register') }}" class="login-register">
                                    還沒有帳號？點此註冊
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
