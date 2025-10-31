<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録 - COACHTECH</title>
    {{-- ここにCSSをリンクします（例：Tailwind CSS、Bootstrap、またはカスタムCSS） --}}
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
   
</head>
<body>
<header>
{{-- ヘッダーとロゴ --}}
    <div class="header">
        <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo" class="logo">
    </div>
</header>
<main>
<div class="container">

    

    {{-- フォームタイトル --}}
    <h1 class="title">ログイン</h1>

    {{-- 登録フォーム --}}
    <form method="POST" action="/login">
        @csrf

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- ログインボタン --}}
        <div class="form-group">
            <button type="submit" class="register-button">
                ログインする
            </button>
        </div>
    </form>

    {{-- ログインリンク --}}
    <div class="login-link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </div>

</div>
</main>
</body>
</html>