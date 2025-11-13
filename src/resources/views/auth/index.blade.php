@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')

    {{-- カテゴリタブ --}}
    <div class="category-tabs">
        <?php 
        $currentTab = $currentTab ?? request()->query('tab', 'recommend'); 
        ?>
        <a href="{{ route('auth.index', ['tab' => 'recommend']) }}" 
           class="tab-item {{ $currentTab === 'recommend' ? 'active' : '' }}">おすすめ</a>
        {{-- 画像に合わせて「マイリスト」をアクティブにします --}}
        <a href="{{ route('auth.index', ['tab' => 'mylist']) }}" 
           class="tab-item {{ $currentTab === 'mylist' ? 'active' : '' }}">マイリスト</a>
    </div>

    {{-- 商品一覧 --}}
    <div class="item-grid">
        @foreach ($items as $item)
        <div class="item-card">
            <a href="/item/{{ $item->id }}">
                {{-- 商品画像プレースホルダー --}}
                <div class="item-image">
                    
                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="item-image-placeholder">
                @if (request()->query('page', 'sell') === 'buy' && $item->is_sold && $item->buyer_id == $user->id)
                        {{-- 「購入した商品」タブで、is_soldがtrueかつ自分が購入者の場合に「SOLD」と表示 --}}
                        <div class="item-sold-overlay">SOLD</div>
                    @elseif (request()->query('page', 'sell') === 'sell' && $item->is_sold)
                        {{-- 「出品した商品」タブで、is_soldがtrueの場合に「SOLD」と表示 --}}
                        <div class="item-sold-overlay">SOLD</div>
                    @endif
                </div>
            </a>
            {{-- 商品名 --}}
            <a href="/item/{{ $item->id }}" class="item-name">{{$item->name}}</a>
        </div>
        @endforeach
    </div>

@endsection