@extends('layouts/adminapp')

@section('title', '登入-台灣森林動態樣區資料管理系統')

@section('header_js')
@endsection

@section('js')
    <script type="text/javascript">
        $('.icon').hide();
        // $("footer").hide();
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
                        @if (session('status'))
                            <div class="login-alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{-- 錯誤訊息 --}}
                        @if ($errors->any())
                            <div class="login-alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="login-form">
                            @csrf

                            <div class="login-field">
                                <label class="login-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="login-input">
                            </div>

                            <div class="login-field">
                                <label class="login-label">密碼</label>
                                <input type="password" name="password" required class="login-input">
                            </div>

                            <div class="login-row">
                                <label class="login-remember">
                                    <input type="checkbox" name="remember">
                                    記住我
                                </label>

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
