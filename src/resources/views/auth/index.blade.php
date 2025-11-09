@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')

    {{-- カテゴリタブ --}}
    <div class="category-tabs">
        <a href="" class="tab-item active">おすすめ</a>
        {{-- 画像に合わせて「マイリスト」をアクティブにします --}}
        <a href="" class="tab-item ">マイリスト</a>
    </div>

    {{-- 商品一覧 --}}
    <div class="item-grid">
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

@endsection