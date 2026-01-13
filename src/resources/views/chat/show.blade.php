@extends('layouts.app')

@section('title', '取引チャット')

@section('css')
<link href="{{ asset('css/chat.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="chat-container">
    {{-- サイドバー: 他の取引一覧 --}}
    <div class="chat-sidebar">
        <h3 class="sidebar-title">その他の取引</h3>
        <div class="sidebar-list">
            @foreach($chat_items as $chat_item)
                <a href="{{ route('chat.show', $chat_item->id) }}" class="sidebar-item">
                    {{-- 画像があれば表示 --}}
                    @if($chat_item->image)
                        <img src="{{ Storage::url($chat_item->image) }}" alt="thumb">
                    @else
                        <div style="width:30px; height:30px; background:#ccc; margin-right:10px;"></div>
                    @endif
                    <span>{{ $chat_item->name }}</span>
                </a>
            @endforeach
            @if($chat_items->isEmpty())
                <p style="font-size:12px; color:#eee; padding:0 15px;">他に取引中の商品はありません</p>
            @endif
        </div>
    </div>

    {{-- チャットメインエリア --}}
    <div class="chat-main">
        
        {{-- ヘッダー: 相手の情報とステータス --}}
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

            {{-- ★修正: ステータス/完了ボタンの出し分け --}}
            @if($item->is_completed)
                <span class="status-completed" style="font-weight:bold; color:#666;">取引完了済み</span>
            
            @elseif($hasRated)
                <span class="status-waiting" style="color:#666; font-size:12px; font-weight:bold;">評価済み（相手の評価待ち）</span>
            
            @else
                {{-- まだ自分が評価していない場合 --}}
                
                @if(Auth::id() === $item->buyer_id)
                    {{-- ケースA: 購入者の場合 -> いつでも「取引を完了する」ボタンを押せる --}}
                    <form action="{{ route('chat.complete', $item->id) }}" method="POST" onsubmit="return confirm('取引を完了しますか？');">
                        @csrf
                        <button type="submit" class="btn-complete">取引を完了する</button>
                    </form>

                @elseif(Auth::id() === $item->user_id)
                    {{-- ケースB: 出品者の場合 --}}
                    
                    @if(isset($partnerHasRated) && $partnerHasRated)
                        {{-- 購入者が既に評価済み -> 出品者も評価ボタンを押せる --}}
                        <form action="{{ route('chat.complete', $item->id) }}" method="POST" onsubmit="return confirm('購入者の評価を行いますか？');">
                            @csrf
                            <button type="submit" class="btn-complete">購入者を評価して取引完了</button>
                        </form>
                    @else
                        {{-- 購入者がまだ評価していない -> 待機メッセージ --}}
                        <span class="status-waiting" style="color:#666; font-size:12px; font-weight:bold;">購入者の受取評価待ち</span>
                    @endif

                @endif
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
                            
                            {{-- 表示用テキスト --}}
                            <div class="msg-text" id="msg-text-{{ $msg->id }}">
                                {!! nl2br(e($msg->content)) !!}
                            </div>

                            {{-- 編集用フォーム（自分のメッセージのみ） --}}
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

                        {{-- 編集・削除リンク（自分のメッセージのみ） --}}
                        @if($msg->user_id === Auth::id())
                            <div class="msg-actions" id="msg-actions-{{ $msg->id }}">
                                <span class="action-link" onclick="startEdit({{ $msg->id }})">編集</span>

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

        {{-- 送信フォーム (取引未完了かつ未評価の場合のみ表示) --}}
        @if(!$item->is_completed && !$hasRated)
        <div class="chat-footer">
            <form action="{{ route('chat.store', $item->id) }}" method="POST" enctype="multipart/form-data" class="chat-form">
                @csrf
                {{-- 入力保持: value="{{ old('content') }}" --}}
                <input type="text" name="content" class="chat-input-text" 
                       placeholder="取引メッセージを記入してください" 
                       value="{{ old('content') }}">
                
                <label class="btn-image-add">
                    画像を追加
                    <input type="file" name="image" accept="image/png, image/jpeg" style="display:none;">
                </label>
                
                <button type="submit" class="btn-send">
                    {{-- 紙飛行機アイコン --}}
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="#666"/>
                    </svg>
                </button>
            </form>
            
            {{-- エラーメッセージ表示 --}}
            @error('content')
                <p style="color:red; font-size:12px; margin-top:5px;">{{ $message }}</p>
            @enderror
            @error('image')
                <p style="color:red; font-size:12px; margin-top:5px;">{{ $message }}</p>
            @enderror
        </div>
        @endif
    </div>
</div>

{{-- 評価モーダル --}}
@if(session('transaction_completed'))
<div class="modal-overlay show">
    <div class="modal-content">
        {{-- ヘッダー --}}
        <div class="modal-header">
            <h3>取引が完了しました。</h3>
        </div>

        <form action="{{ route('review.store', $item->id) }}" method="POST">
            @csrf
            {{-- ボディ --}}
            <div class="modal-body">
                <p>今回の取引相手はどうでしたか？</p>
                <div class="star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                {{-- 星の値を格納する隠しフィールド --}}
                <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>

            {{-- フッター --}}
            <div class="modal-footer">
                <button type="submit" class="btn-modal-submit">送信する</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    // ページ読み込み時に最下部へスクロール
    const container = document.getElementById('message-container');
    if(container) container.scrollTop = container.scrollHeight;

    // 星評価の制御
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('ratingInput');
    
    function updateStars(val) {
        stars.forEach(s => {
            if(s.getAttribute('data-value') <= val) s.classList.add('active');
            else s.classList.remove('active');
        });
    }
    
    if(stars.length > 0) {
        // 初期値5
        updateStars(5);
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                ratingInput.value = val;
                updateStars(val);
            });
        });
    }

    // メッセージ編集モードの切り替え
    function startEdit(id) {
        document.getElementById('msg-text-' + id).style.display = 'none';
        document.getElementById('msg-actions-' + id).style.display = 'none';
        document.getElementById('edit-form-' + id).style.display = 'block';
    }

    function cancelEdit(id) {
        document.getElementById('msg-text-' + id).style.display = 'block';
        document.getElementById('msg-actions-' + id).style.display = 'block';
        document.getElementById('edit-form-' + id).style.display = 'none';
    }
</script>
@endsection