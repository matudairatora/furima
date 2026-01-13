<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH サービス')</title>
    {{-- 共通CSSを読み込みます --}}
    <link href="{{ asset('css/common.css') }}" rel="stylesheet">
    @yield('css')
</head>
<body>

{{-- ヘッダー部分を直接記述 --}}
<header>
    <div class="header-container">
        <div class="header-content">
            {{-- ロゴエリア (常に表示) --}}
            <div class="logo-area">
                <a href="/">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo" class="logo">
                </a>
            </div>

            @if (!request()->routeIs('chat.show'))

                {{-- 検索エリア --}}
                <div class="search-area">
                    <form action="{{ route('auth.index') }}" method="GET">
                        <input type="text" 
                            name="keyword" 
                            placeholder="なにをお探しですか？" 
                            class="search-input" 
                            value="{{ request('keyword') }}"
                        >
                    </form>
                </div>

                {{-- ナビゲーションエリア --}}
                <div class="nav-area">
                    @auth
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                        </form>
                        <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                        <a href="{{ route('auth.mypage') }}" class="nav-link">マイページ</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">ログイン</a>
                        <a href="{{ route('auth.mypage') }}" class="nav-link">マイページ</a>
                    @endauth    
                        <a href="{{ route('auth.item') }}" class="submit-button">出品</a>
                </div>

            @endif

        </div>
    </div>
</header>

<main class="main-container">
    @yield('content')
</main>
 @stack('scripts')
</body>
</html>