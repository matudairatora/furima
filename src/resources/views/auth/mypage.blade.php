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
            
            @if ($user->mypage && $user->mypage->mypageimage)
                                
            <img src="{{ Storage::url($user->mypage->mypageimage) }}" alt="{{ $user->name ?? 'User' }}のプロフィール画像"  class="profile-avatar-image">
            @else
            <div class="default-avatar-placeholder"></div>
            @endif
        </div>
        <div class="profile-info">
            <span class="username">{{ $user->name ?? ''}}</span>
            <a href="{{ route('profile.edit') }}" class="edit-button">プロフィールを編集</a>
        </div>
    </div>

    {{-- タブセクション --}}
    <div class="tabs">
        <?php 
    $currentTab = request()->query('page', 'sell'); 
    ?>
        {{-- アクティブなタブ (出品した商品) --}}
        <a href="{{ route('auth.mypage', ['page' => 'sell']) }}" 
       class="tab-item {{ $currentTab === 'sell' ? 'active' : '' }}">出品した商品</a>
        {{-- 非アクティブなタブ (購入した商品) --}}
        <a href="{{ route('auth.mypage', ['page' => 'buy']) }}" 
       class="tab-item {{ $currentTab === 'buy' ? 'active' : '' }}">購入した商品</a>
    </div>

    {{-- 商品一覧セクション --}}
    <div class="item-list">
     
        @foreach ($items as $item)
        <div class="item-card">
            <a href="/item/{{ $item->id }}">
                {{-- 商品画像プレースホルダー --}}
                <div class="item-image">
                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="item-image-placeholder">   
                @if (request()->query('page', 'sell') === 'buy')
                    <div class="item-sold-overlay">SOLD</div>
                @elseif (request()->query('page', 'sell') === 'sell' && $item->isSold())
                    <div class="item-sold-overlay">SOLD</div>
                @endif
                </div>
            </a>
            {{-- 商品名 --}}
            <a href="/item/{{ $item->id }}" class="item-name">{{$item->name}}</a>
        </div>
        @endforeach
    </div>

</div>
@endsection