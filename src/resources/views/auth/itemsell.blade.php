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
                <img src="{{ asset($item->image) }}" alt="商品画像" onerror="this.onerror=null;this.src='https://placehold.co/400x400/eeeeee/333333?text=商品画像';" class="product-image">
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
                <span class="likes">
                    <i class="far fa-heart"></i> {{ $item->favorite_count ?? 3 }}
                </span>
                <span class="comments">
                    <i class="far fa-comment"></i> {{ $item->comments_count ?? 1 }}
                </span>
            </div>

            {{-- 購入手続きボタン --}}
            <button class="purchase-button">購入手続きへ</button>

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
                    <p>カテゴリ： <span class="tag">洋服</span><span class="tag">メンズ</span></p>
                    <p>商品の状態：{{$item->condition->condition}}</p>
                </div>
            </section>
        </div>
    </div>

    {{-- コメントセクション --}}
    <div class="comment-section">
        <h2 class="section-title comment-title">コメント({{ $item->comments_count ?? 1 }})</h2>
        
        {{-- コメント一覧 (ここでは1つのみ表示) --}}
        <div class="comment-list">
            <div class="comment-item">
                <div class="comment-header">
                    <span class="comment-user">admin</span>
                </div>
                {{-- コメント入力エリアのプレースホルダーを再現 --}}
                <div class="comment-text-placeholder">
                    こちらにコメントが入ります。
                </div>
            </div>
        </div>

        {{-- コメント投稿フォーム --}}
        <h2 class="section-title comment-form-title">商品へのコメント</h2>
        <form action="/item/{{ $item->id }}/comment" method="POST" class="comment-form">
            {{-- @csrf --}}
            <textarea name="comment" rows="5" placeholder="コメントを入力してください..." class="comment-textarea"></textarea>
            <button type="submit" class="comment-submit-button">コメントを送信する</button>
        </form>
    </div>
</div>
@endsection