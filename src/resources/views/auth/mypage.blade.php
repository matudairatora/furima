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
            @if ($user->mypage && $user->mypage->mypage_image)
            <img src="{{ Storage::url($user->mypage->mypage_image) }}" alt="{{ $user->name ?? 'User' }}のプロフィール画像" class="profile-avatar-image">
            @else
            <div class="default-avatar-placeholder"></div>
            @endif
        </div>
        <div class="profile-info">
            <div class="profile-text">
                <span class="username">{{ $user->name ?? ''}}</span>
                {{-- ★★★ 評価（星）の表示 (FN005) ★★★ --}}
                <div class="user-rating">
                    {{-- コントローラーで $averageRating を計算して渡す想定 --}}
                    @php
                        // 仮の数値（本来はControllerで計算）
                        $rating = $averageRating ?? 0; 
                        $fullStars = round($rating);
                    @endphp
                    <span class="stars">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $fullStars)
                                <span class="star filled">★</span>
                            @else
                                <span class="star">☆</span>
                            @endif
                        @endfor
                    </span>
                    
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="edit-button">プロフィールを編集</a>
        </div>
    </div>

    {{-- タブセクション --}}
    <div class="tabs">
        <?php 
        // デフォルトを 'sell' にしつつ、trading も受け付ける
        $currentTab = request()->query('page', 'sell'); 
        ?>
        <a href="{{ route('auth.mypage', ['page' => 'sell']) }}" 
           class="tab-item {{ $currentTab === 'sell' ? 'active' : '' }}">出品した商品</a>
        <a href="{{ route('auth.mypage', ['page' => 'buy']) }}" 
           class="tab-item {{ $currentTab === 'buy' ? 'active' : '' }}">購入した商品</a>
        
        {{-- ★★★ 取引中の商品タブ (バッジスタイルを直接指定) ★★★ --}}
        <a href="{{ route('auth.mypage', ['page' => 'trading']) }}" 
           class="tab-item {{ $currentTab === 'trading' ? 'active' : '' }}">
           取引中の商品
           @if(isset($totalUnread) && $totalUnread > 0)
               {{-- ここで強制的に赤丸スタイルを適用 --}}
               <span class="tab-badge" style="background-color: #ff0000; color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; font-size: 12px; margin-left: 5px; font-weight: bold; line-height: 1;">
                   {{ $totalUnread }}
               </span>
           @endif
        </a>
    </div>

    {{-- 商品一覧セクション --}}
    <div class="item-list">
        @foreach ($items as $item)
        <div class="item-card">
            {{-- 取引中タブの場合、チャット画面へ遷移させる場合があるのでリンク先を分岐 --}}
            <a href="{{ $currentTab === 'trading' ? route('chat.show', ['item_id' => $item->id]) : '/item/' . $item->id }}">
                <div class="item-image">
                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="item-image-placeholder">   
                    
                    @if ($currentTab === 'buy')
                        <div class="item-sold-overlay">SOLD</div>
                    @elseif ($currentTab === 'sell' && $item->isSold())
                        <div class="item-sold-overlay">SOLD</div>
                    @endif

                    {{-- ★★★ 画像上の通知バッジ (スタイルを直接指定) ★★★ --}}
                    @if($currentTab === 'trading' && isset($item->unread_count) && $item->unread_count > 0)
                        {{-- ここで強制的に赤丸スタイルを適用 --}}
                        <div class="notification-badge" style="position: absolute; top: 0; left: 0; background-color: #ff0000; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 12px; font-weight: bold; z-index: 20; box-shadow: 1px 1px 3px rgba(0,0,0,0.3);">
                            {{ $item->unread_count }}
                        </div>
                    @endif
                </div>
            </a>
            <a href="{{ $currentTab === 'trading' ? route('chat.show', ['item_id' => $item->id]) : '/item/' . $item->id }}" class="item-name">
                {{ $item->name }}
            </a>
        </div>
        @endforeach
    </div>

</div>
@endsection