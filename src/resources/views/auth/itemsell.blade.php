@extends('layouts.app')

@section('title', $item->name)

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/itemsell.css') }}">
@endsection

@section('content')
<div class="item-detail-container">
    <div class="item-content-wrap">
        {{-- 左側: 商品画像 --}}
        <div class="item-image-area">
            {{-- 添付画像のレイアウトに合わせ、placeholderを使用 --}}
            <div class="product-image-placeholder">
            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="product-image">    
            </div>
        </div>

        {{-- 右側: 商品情報 --}}
        <div class="item-info-area">
            {{-- 商品名とブランド名 --}}
            <h1 class="item-name">{{ $item->name }}</h1>
            <p class="brand-name">{{$item->brand}}</p>
            
            {{-- 価格 --}}
            <div class="price">
                ¥{{ number_format($item->price) }} <span class="tax-info">(税込)</span>
            </div>

            {{-- いいねとコメント数 --}}
            <div class="interaction-status">
                <form action="{{ route('item.toggle_favorite', ['itemId' => $item->id]) }}" method="POST" class="favorite-form">
                    @csrf
                    
                    {{-- 認証ユーザーがお気に入り登録しているかチェック --}}
                    @php
                        // $item->favoritesコレクションに現在のユーザーIDが含まれているか確認
                        $isFavorite = Auth::check() && $item->favorites->contains(Auth::id()); 
                        $iconClass = $isFavorite ? 'fas fa-heart' : 'far fa-heart'; // fas: 塗りつぶし (お気に入り済み), far: 枠線 (未登録)
                    @endphp

                    {{-- ボタンをクリックするとPOSTリクエストが送信され、toggleFavoriteメソッドが実行される --}}
                    <button type="submit" class="favorite-button {{ $isFavorite ? 'favorited' : '' }}">
                        <i class="{{ $iconClass }}"></i>
                    </button>
                </form>
                
                <span class="likes-count">
                    {{-- お気に入り数 (favoritesリレーションの要素数をカウント) --}}
                    {{ $item->favorites->count() }}
                </span>
                <span class="comments">
                    <i class="far fa-comment"></i> {{ $item->comments->count()}}
                </span>
            </div>

            {{-- 購入手続きボタン --}}
            <a href="{{ route('item.purchase', ['itemId' => $item->id]) }}" class="purchase-button-link">
            <button class="purchase-button">購入手続きへ</button>
            </a>
            {{-- 商品説明 --}}
            <section class="description-section">
                <h2 class="section-title">商品説明</h2>
                <div class="description-content">
                    {{$item->explanation}}
                </div>
            </section>

            {{-- 商品の情報 --}}
            <section class="info-section">
                <h2 class="section-title">商品の情報</h2>
                <div class="info-details">
                <p>カテゴリ：
                @foreach($item->categories as $category)    
                <span class="tag">{{$category->content}}</span>
                @endforeach
                </p>
                    <p>商品の状態：{{$item->condition->condition}}</p>
                </div>
            </section>
       
   

            {{-- コメントセクション --}}
            <div class="comment-section">
                <h2 class="section-title comment-title">コメント({{ $item->comments->count() }})</h2>
                
                {{-- コメント一覧 (ここでは1つのみ表示) --}}
                <div class="comment-list">
                    {{-- コントローラーで取得したコメントコレクションをループ --}}
                    @foreach($item->comments as $comment)
                    <div class="comment-item">
                        <div class="comment-header">
                            {{-- ★ ユーザー名を表示する ★ --}}
                            <div class="comment-mypage">
                           
                                @if ($comment->user->mypage && $comment->user->mypage->mypage)
                                
                                <img src="{{ Storage::url($comment->user->mypage->mypage) }}" alt="{{ $user->name ?? 'User' }}のプロフィール画像"  class="profile-avatar-image">
                                @else
                                {{-- 画像がない場合のデフォルトのプレースホルダーなどを表示 (CSSで装飾してください) --}}
                                <div class="default-avatar-placeholder"></div>
                                @endif
                            </div>
                            <span class="comment-user">{{ $comment->user->name ?? '退会済みユーザー' }}</span>
                        </div>
                        <div class="comment-text-placeholder">
                            {{-- ★ 実際のコメント内容を表示する ★ --}}
                            {{ $comment->comment }}
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- コメント投稿フォーム --}}
                <h2 class="section-title comment-form-title">商品へのコメント</h2>
                <form action="/item/{{ $item->id }}/comment" method="POST" class="comment-form">
                    @csrf 
                    <textarea name="comment" rows="5" placeholder="コメントを入力してください..." class="comment-textarea"></textarea>
                    <button type="submit" class="comment-submit-button">コメントを送信する</button>
                    @error('comment')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                     @enderror
                </form>
            </div>
         </div>
     </div>
</div>
@endsection