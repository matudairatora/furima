@extends('layouts.app')

@section('title', '取引チャット')

@section('css')
<link href="{{ asset('css/chat.css') }}" rel="stylesheet">
<style>
    /* 編集フォーム用の追加スタイル */
    .edit-form {
        display: none; /* 初期状態は非表示 */
        width: 100%;
    }
    .edit-textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 5px;
    }
    .btn-save, .btn-cancel {
        font-size: 12px;
        padding: 4px 8px;
        cursor: pointer;
        border-radius: 4px;
        border: none;
    }
    .btn-save {
        background-color: #ff5555;
        color: white;
    }
    .btn-cancel {
        background-color: #ccc;
        color: #333;
    }
    /* 削除ボタンの見た目をリンク風にするリセットCSS */
    .btn-delete-link {
        background: none;
        border: none;
        color: #888;
        font-size: 10px;
        cursor: pointer;
        padding: 0;
        margin-left: 10px; /* デザイン画に合わせる */
    }
    .btn-delete-link:hover {
        text-decoration: underline;
        color: #ff5555;
    }
</style>
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
                <span class="status-completed">取引完了済み</span>
            @elseif($hasRated)
                <span class="status-waiting" style="color:#666; font-size:12px; font-weight:bold;">評価済み（相手の評価待ち）</span>
            @else
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
                    
                    {{-- 相手のアイコン --}}
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
                        {{-- 送信者名 --}}
                        <p class="msg-sender-name">
                            {{ $msg->user_id === Auth::id() ? ($user->name ?? '') : ($partner->name ?? '') }}
                        </p>

                        <div class="message-bubble">
                            @if($msg->image)
                                <img src="{{ Storage::url($msg->image) }}" class="msg-image">
                            @endif
                            
                            {{-- ★修正: 表示用テキストエリア --}}
                            <div class="msg-text" id="msg-text-{{ $msg->id }}">
                                {!! nl2br(e($msg->content)) !!}
                            </div>

                            {{-- ★追加: 編集用フォーム（自分のメッセージのみ） --}}
                            @if($msg->user_id === Auth::id())
                                <form action="{{ route('chat.message.update', $msg->id) }}" method="POST" class="edit-form" id="edit-form-{{ $msg->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="content" class="edit-textarea" rows="3">{{ $msg->content }}</textarea>
                                    <div class="edit-actions">
                                        <button type="button" class="btn-cancel" onclick="cancelEdit({{ $msg->id }})">キャンセル</button>
                                        <button type="submit" class="btn-save">保存</button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- ★追加: 編集・削除リンクエリア（自分のメッセージのみ表示） --}}
                        @if($msg->user_id === Auth::id())
                            <div class="msg-actions" id="msg-actions-{{ $msg->id }}">
                                {{-- 編集ボタン --}}
                                <span class="action-link" onclick="startEdit({{ $msg->id }})">編集</span>

                                {{-- 削除ボタン（フォームで実装） --}}
                                <form action="{{ route('chat.message.destroy', $msg->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('本当に削除しますか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-link">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- 自分のアイコン --}}
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
        {{-- ヘッダー（タイトルと下線） --}}
        <div class="modal-header">
            <h3>取引が完了しました。</h3>
        </div>

        <form action="{{ route('review.store', $item->id) }}" method="POST">
            @csrf
            {{-- ボディ（説明文と星） --}}
            <div class="modal-body">
                <p>今回の取引相手はどうでしたか？</p>
                <div class="star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>

            {{-- フッター（上線と右寄せボタン） --}}
            <div class="modal-footer">
                <button type="submit" class="btn-modal-submit">送信する</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    // メッセージエリアの最下部へスクロール
    const container = document.getElementById('message-container');
    if(container) container.scrollTop = container.scrollHeight;

    // 星評価のスクリプト
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

    // ★追加: 編集モード切り替え用スクリプト
    function startEdit(id) {
        // テキストと編集/削除リンクを隠す
        document.getElementById('msg-text-' + id).style.display = 'none';
        document.getElementById('msg-actions-' + id).style.display = 'none';
        // 編集フォームを表示する
        document.getElementById('edit-form-' + id).style.display = 'block';
    }

    function cancelEdit(id) {
        // テキストと編集/削除リンクを表示する
        document.getElementById('msg-text-' + id).style.display = 'block';
        document.getElementById('msg-actions-' + id).style.display = 'block';
        // 編集フォームを隠す
        document.getElementById('edit-form-' + id).style.display = 'none';
    }
</script>
@endsection