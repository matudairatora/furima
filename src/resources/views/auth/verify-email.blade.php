<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH サービス')</title>
    {{-- 共通CSSを読み込みます --}}
    <link href="{{ asset('css/common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/verify.css') }}" rel="stylesheet">
</head>
<body>

{{-- ヘッダー部分を直接記述 --}}
<header>
    <div class="header-container">
        <div class="header-content">
            {{-- ロゴエリア (画像上部の黒帯部分) --}}
            <div class="logo-area">
                <a href="/">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo" class="logo">
                </a>
            </div>
        </div>
    </div>
</header>

<main class="main-container">

<div class="verify-container">
    <div class="verify-content">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- 
            画像の「認証はこちらから」ボタン
            ※実際の認証はメール内のリンクで行いますが、
            ここでは開発用にMailHogを開くリンク、または装飾として配置します。
        --}}
        <a href="http://localhost:8025/" target="_blank" rel="noopener noreferrer" class="verify-button">
            認証はこちらから
        </a>

        {{-- 再送信リンク --}}
        <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
            @csrf
            <button type="submit" class="resend-link">
                認証メールを再送信する
            </button>
        </form>
    </div>
</div>
</main>
</body>
</html>