@extends('layouts.app') 

@section('title', '住所の変更')

@section('css')
<link href="{{ asset('css/address_edit.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="address-edit-container">
    <h2 class="address-edit-title">住所の変更</h2>

    {{-- フォームのアクションURLはプロジェクトに合わせて適宜変更してください。例: route('address.update') --}}
    <form action="{{ route('address.update') }}" method="POST">
        @csrf {{-- CSRF対策 --}}
            
            {{-- ★ 修正点: itemIdをPOSTリクエストに含める hidden フィールドを追加 ★ --}}
            <input type="hidden" name="item_id" value="{{ $itemId ?? '' }}">
        <div class="address-form-content">
            
            {{-- 郵便番号 --}}
            <div class="form-group">
                <label for="postcode">郵便番号</label>
                {{-- $user->mypage->postcode は profile.blade.php からの推測 --}}
                <input type="text" id="postcode" name="postcode" class="form-input" 
                       value="{{ old('postcode', $user->mypage->postcode ?? '')}}">
            </div>
             {{-- エラーメッセージの表示 (profile.blade.phpの形式を継承) --}}
             @error('postcode')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            {{-- 住所 --}}
            <div class="form-group">
                <label for="address">住所</label>
                {{-- $user->mypage->address は profile.blade.php からの推測 --}}
                <input type="text" id="address" name="address" class="form-input" 
                       value="{{ old('address', $user->mypage->address ?? '')}}">
            </div>
             @error('address')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            {{-- 建物名 --}}
            <div class="form-group">
                <label for="building">建物名</label>
                {{-- $user->mypage->building は profile.blade.php からの推測 --}}
                <input type="text" id="building" name="building" class="form-input" 
                       value="{{ old('building', $user->mypage->building ?? '')}}">
            </div>

            {{-- 更新ボタン --}}
            <div class="form-action">
                <button type="submit" class="update-button">更新する</button>
            </div>
        </div>
    </form>
</div>
@endsection