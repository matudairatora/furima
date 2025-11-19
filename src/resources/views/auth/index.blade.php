@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
@endsection

@section('content')

    <div class="category-tabs">
        <?php 
        $currentTab = $currentTab ?? request()->query('tab', 'recommend'); 
        $keyword = request()->query('keyword');
        ?>
        <a href="{{ route('auth.index', ['tab' => 'recommend', 'keyword' => $keyword]) }}" 
           class="tab-item {{ $currentTab === 'recommend' ? 'active' : '' }}">おすすめ</a>
           
        <a href="{{ route('auth.index', ['tab' => 'mylist', 'keyword' => $keyword]) }}" 
           class="tab-item {{ $currentTab === 'mylist' ? 'active' : '' }}">マイリスト</a>
    </div>

    {{-- 商品一覧 --}}
    <div class="item-grid">
        @foreach ($items as $item)
        <div class="item-card">
            <a href="/item/{{ $item->id }}">
                
                <div class="item-image">
                    
                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="item-image-placeholder">
                @if ($item->isSold()) 
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