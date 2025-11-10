@extends('layouts.app')

@section('title', 'マイページ')
@section('css')
<link href="{{ asset('css/mypage.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="main-container">

    {{-- ユーザープロフィールセクション --}}
    <div class="profile-section">
        <div class="profile-avatar">
            {{-- ここにユーザーのアバター画像が入ります。画像がなければグレーの円を表示 --}}
        </div>
        <div class="profile-info">
            <span class="username">ユーザー名</span>
            <a href="{{ route('profile.edit') }}" class="edit-button">プロフィールを編集</a>
        </div>
    </div>

    {{-- タブセクション --}}
    <div class="tabs">
        {{-- アクティブなタブ (出品した商品) --}}
        <a href="#" class="tab-item active">出品した商品</a>
        {{-- 非アクティブなタブ (購入した商品) --}}
        <a href="#" class="tab-item">購入した商品</a>
    </div>

    {{-- 商品一覧セクション --}}
    <div class="item-list">
     
        @foreach ($items as $item)
        <div class="item-card">
            <a href="/item/{{ $item->id }}">
                {{-- 商品画像プレースホルダー --}}
                <div class="item-image">
                <img src="{{asset($item->image)}}" alt="商品画像" class="item-image-placeholder">    
                
                </div>
            </a>
            {{-- 商品名 --}}
            <a href="/item/{{ $item->id }}" class="item-name">{{$item->name}}</a>
        </div>
        @endforeach
    </div>

</div>
@endsection