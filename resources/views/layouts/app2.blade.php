@extends('layouts/adminapp')

@section('css')
    <style>
        /* 一般連結樣式 */
        .nav-link {
            font-size: 14px;
            font-weight: 400;
            color: #e5e7eb;
            /* 你原本 hover:text-white 附近的色系 */
            text-decoration: none;
            transition: color .15s ease, font-weight .15s ease;
        }

        /* hover 效果 */
        .nav-link:hover {
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
        }

        /* form 不要破壞 flex 排版 */
        .nav-form {
            margin: 0;
        }
    </style>
@endsection

@section('footer')
    <footer>
        @php
            $hasPendingUsers = App\Models\User::where('status', 'pending')->exists();
        @endphp
        <div id="header_text" class='fc-w flex'
            style='font-size: 14px; align-items: center; justify-content: space-between;  box-sizing: border-box;'>
            <div>
                <p>如有任何問題，請洽 @kris1014</a></p>
                {{-- @if (session('latest_update'))
                <p style='margin-left: 100px;'>更新日期：{{ session('latest_update') }}</p>
            @endif --}}

            </div>
            <div class='flex' style='gap:12px; align-items: center;'>

                @if (auth()->user()?->is_admin && $hasPendingUsers)
                    <span style="color: #f87171; font-weight: 600;">
                        有待審使用者!!
                    </span>
                @endif

                @if (auth()->check())
                    <a href="{{ route('admin.profile.edit') }}" class="nav-link" style='text-decoration: none;'>
                        個人資訊
                    </a>
                @endif

                @if (auth()->user()?->is_admin)
                    <a href="{{ route('admin.users.index') }}" class="nav-link" style='text-decoration: none;'>
                        使用者管理
                    </a>
                @endif

                {{-- <a href="{{ route('admin.password.force.edit') }}" class="nav-link" style='text-decoration: none;'>
                    更換密碼
                </a> --}}

                <form method="POST" action="{{ route('logout') }}" class="nav-form">
                    @csrf
                    <button type="submit" class="btn-secondary">登出</button>
                </form>



            </div>
        </div>
    </footer>
@endsection
