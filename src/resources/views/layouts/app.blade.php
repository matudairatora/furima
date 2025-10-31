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
            {{-- ロゴエリア (画像上部の黒帯部分) --}}
            <div class="logo-area">
                <a href="">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo" class="logo">
                </a>
            </div>

            {{-- 検索エリア --}}
            <div class="search-area">
                <input type="text" placeholder="なにをお探しですか？" class="search-input">
            </div>

            {{-- ナビゲーションエリア --}}
            <div class="nav-area">
                <a href="{{ route('logout') }}" class="nav-link">ログアウト</a>
                <a href="" class="nav-link">マイページ</a>
                <a href="" class="submit-button">出品</a>
            </div>
        </div>
    </div>
</header>

<main class="main-container">
    
    @yield('content')
</main>

</body>
</html>