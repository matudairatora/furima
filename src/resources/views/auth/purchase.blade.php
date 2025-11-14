@extends('layouts.app')

@section('title', '購入')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-page-container">
    <h1 class="page-title">購入</h1>
    
    <div class="purchase-content-wrap">
        <form method="POST" action="{{ route('item.process_purchase', ['itemId' => $item->id]) }}">
            @csrf
        {{-- 左側: 商品詳細と支払い・配送先情報 --}}
            <div class="purchase-info-area">
                
                {{-- 商品詳細エリア (商品画像、商品名、価格) --}}
                <div class="product-summary">
                    <div class="product-image-placeholder">
                        <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="product-image"> 
                    </div>
                    <div class="product-details">
                        <h2 class="product-name">{{ $item->name }}</h2>
                        <p class="product-price">¥{{ number_format($item->price) }}</p>
                    </div>
                </div>

                <hr class="separator">

                {{-- 支払い方法の選択 --}}
                <section class="payment-method-section">
                    <h2 class="section-title">支払い方法</h2>
                    <div class="payment-selection-wrap">
                        {{-- 添付画像のように、ドロップダウン風に選択肢を配置 --}}
                        <select class="payment-dropdown" name="payment_method" id="payment-method-select">
                            <option value="convenience" selected>コンビニ払い</option>
                            <option value="card">カード払い</option>
                        </select>
                    </div>
                    @error('payment_method')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                     @enderror
                </section>

                <hr class="separator">

                {{-- 配送先情報 --}}
                <section class="shipping-address-section">
                    <h2 class="section-title">配送先</h2>
                    <a href="{{route('address.edit',['itemId' => $item->id])}}" class="change-link">変更する</a>
                    <div class="address-details">
                        <p>〒 {{ $userAddress['postcode'] ?? '未登録' }}</p>
                        <p>{{ $userAddress['address_line'] ?? '配送先情報がありません' }}</p>
                    </div>
                    <input type="hidden" name="user_address" value="{{ $userAddress['address_line'] ?? '' }}">
                     @error('user_address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                     @enderror
                </section>

                <hr class="separator">
            </div>

            {{-- 右側: 決済エリア (注文内容の確認) --}}
            <div class="payment-summary-area">
            <div class="order-summary-item">
                <div class="order-summary-box">
                    <div class="summary-line">
                        <span class="summary-label">{{ $item->name }}</span>
                        <span class="summary-value">¥{{ number_format($item->price) }}</span>
                    </div>
                </div>
                <div class="order-summary-box">
                    <div class="summary-line">
                        <span class="summary-label">支払い方法</span>
                        <span class="summary-value" id="selected-payment-display">コンビニ払い</span>
                    </div>
                </div>
                </div>
                {{-- 購入ボタン --}}
                <button type="submit" class="purchase-action-button">購入する</button>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectElement = document.getElementById('payment-method-select');
        const displayElement = document.getElementById('selected-payment-display');

        selectElement.addEventListener('change', function () {
            const selectedText = selectElement.options[selectElement.selectedIndex].text;
            displayElement.textContent = selectedText;
        });
    });
</script>
@endpush