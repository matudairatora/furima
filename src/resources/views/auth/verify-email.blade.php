@extends('layouts.app')

@section('css')
<link href="{{ asset('css/verify.css') }}" rel="stylesheet">
@endsection

@section('content')
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
        <a href="{{ route('profile.edit') }}" class="verify-button">
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
@endsection