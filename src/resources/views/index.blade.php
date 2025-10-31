@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')

    {{-- カテゴリタブ --}}
    <div class="category-tabs">
        <a href="" class="tab-item">おすすめ</a>
        {{-- 画像に合わせて「マイリスト」をアクティブにします --}}
        <a href="" class="tab-item active">マイリスト</a>
    </div>

    {{-- 商品一覧 --}}
    <div class="item-grid">
        @for ($i = 0; $i < 3; $i++)
        <div class="item-card">
            <a href="">
                {{-- 商品画像プレースホルダー --}}
                <div class="item-image-placeholder">
                    <span>商品画像</span>
                </div>
            </a>
            {{-- 商品名 --}}
            <a href="" class="item-name">商品名</a>
        </div>
        @endfor
    </div>

@endsection