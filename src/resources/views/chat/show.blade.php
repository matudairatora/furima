@extends('layouts.app')

@section('title', '取引チャット')

@section('css')
<link href="{{ asset('css/chat.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="chat-container">
    {{-- サイドバー --}}
    <div class="chat-sidebar">
        <h3 class="sidebar-title">その他の取引</h3>
        <div class="sidebar-list">
            @foreach($chat_items as $chat_item)
                <a href="{{ route('chat.show', $chat_item->id) }}" class="sidebar-item">
                    <img src="{{ Storage::url($chat_item->image) }}" alt="thumb">
                    <span>{{ $chat_item->name }}</span>
                </a>
            @endforeach
            @if($chat_items->isEmpty())
                <p style="font-size:12px; color:#666;">他に取引中の商品はありません</p>
            @endif
        </div>
    </div>

    {{-- チャットエリア --}}
    <div class="chat-main">
        
        <div class="chat-header-user">
            <div class="user-info">
                <div class="user-avatar">
                    @if($partner->mypage && $partner->mypage->mypage_image)
                        <img src="{{ Storage::url($partner->mypage->mypage_image) }}">
                    @else
                        <div class="default-avatar"></div>
                    @endif
                </div>
                <span class="user-name">「{{ $partner->name }}」さんとの取引画面</span>
            </div>

            {{-- ステータス表示 --}}
            @if($item->is_completed)
                {{-- 双方が評価完了済み --}}
                <span class="status-completed">取引完了済み</span>
            
            @elseif($hasRated)
                {{-- 自分は評価したが、相手がまだ --}}
                <span class="status-waiting" style="color:#666; font-size:12px; font-weight:bold;">評価済み（相手の評価待ち）</span>
            
            @else
                {{-- まだ取引中（自分が完了ボタンを押していない） --}}
                <form action="{{ route('chat.complete', $item->id) }}" method="POST" onsubmit="return confirm('取引を完了しますか？');">
                    @csrf
                    <button type="submit" class="btn-complete">取引を完了する</button>
                </form>
            @endif
        </div>

        {{-- 商品情報 --}}
        <div class="chat-item-info">
            <div class="item-img">
                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}">
            </div>
            <div class="item-details">
                <p class="item-name">{{ $item->name }}</p>
                <p class="item-price">¥{{ number_format($item->price) }}</p>
            </div>
        </div>

        {{-- メッセージ一覧 --}}
        <div class="chat-messages" id="message-container">
            @foreach($messages as $msg)
                <div class="message-row {{ $msg->user_id === Auth::id() ? 'row-self' : 'row-partner' }}">
                    @if($msg->user_id !== Auth::id())
                        <div class="msg-avatar">
                            @if($partner->mypage && $partner->mypage->mypage_image)
                                <img src="{{ Storage::url($partner->mypage->mypage_image) }}">
                            @else
                                <div class="default-avatar"></div>
                            @endif
                        </div>
                    @endif
                    <div class="message-body">
                        {{-- ★修正: 自分の場合は $user->name、相手の場合は $partner->name を表示 --}}
                        <p class="msg-sender-name">
                            {{ $msg->user_id === Auth::id() ? ($user->name ?? '') : ($partner->name ?? '') }}
                        </p>
                        <div class="message-bubble">
                            @if($msg->image)
                                <img src="{{ Storage::url($msg->image) }}" class="msg-image">
                            @endif
                            {!! nl2br(e($msg->content)) !!}
                        </div>
                    </div>
                    @if($msg->user_id === Auth::id())
                        <div class="msg-avatar">
                             @if(Auth::user()->mypage && Auth::user()->mypage->mypage_image)
                                <img src="{{ Storage::url(Auth::user()->mypage->mypage_image) }}">
                            @else
                                <div class="default-avatar"></div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- 送信フォーム --}}
        @if(!$item->is_completed && !$hasRated)
        <div class="chat-footer">
            <form action="{{ route('chat.store', $item->id) }}" method="POST" enctype="multipart/form-data" class="chat-form">
                @csrf
                <input type="text" name="content" class="chat-input-text" placeholder="取引メッセージを記入してください">
                <label class="btn-image-add">
                    画像を追加
                    <input type="file" name="image" accept="image/png, image/jpeg" style="display:none;">
                </label>
                <button type="submit" class="btn-send">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="#666"/>
                    </svg>
                </button>
            </form>
            @error('content') <p style="color:red; font-size:12px; margin-top:5px;">{{ $message }}</p> @enderror
        </div>
        @endif
    </div>
</div>

{{-- 評価モーダル --}}
@if(session('transaction_completed'))
<div class="modal-overlay show">
    <div class="modal-content">
        <h3>取引が完了しました</h3>
        <p>今回の取引相手はどうでしたか？</p>
        <form action="{{ route('review.store', $item->id) }}" method="POST">
            @csrf
            <div class="star-rating">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">
            <button type="submit" class="btn-complete" style="width:100%; margin-top:10px;">評価を送信</button>
        </form>
    </div>
</div>
@endif
<script>
    const container = document.getElementById('message-container');
    if(container) container.scrollTop = container.scrollHeight;

    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('ratingInput');
    
    function updateStars(val) {
        stars.forEach(s => {
            if(s.getAttribute('data-value') <= val) s.classList.add('active');
            else s.classList.remove('active');
        });
    }
    if(stars.length > 0) {
        updateStars(5);
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                ratingInput.value = val;
                updateStars(val);
            });
        });
    }
</script>
@endsection