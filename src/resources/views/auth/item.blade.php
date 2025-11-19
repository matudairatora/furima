@extends('layouts.app')
@section('css')
<link href="{{ asset('css/item.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="item-create-container">
    <h1 class="page-title">商品の出品</h1>

    <form action="{{ route('item.create') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 商品画像 --}}
        <section class="form-section">
            <h2 class="section-title">商品画像</h2>
            <div class="image-upload-area">
                <label for="image-upload" class="image-select-button">
                    画像を選択する
                </label>
                <input type="file" id="image-upload" name="image" style="display: none;">
            </div>
            @error('image')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </section>

        {{-- 商品の詳細 --}}
        <section class="form-section">
            <h2 class="section-title-details">商品の詳細</h2>

            {{-- カテゴリー --}}
            <div class="form-group">
                <h3 class="subsection-title">カテゴリー</h3>
                <div class="category-tags">
                    @foreach($categories as $category)
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ old('category_id')==$category->id ? 'selected' : '' }}><span>{{$category->content }}</span></label>
                    @endforeach
                </div>
                @error('categories')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            </div>

            {{-- 商品の状態 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品の状態</h3>
                <div class="select-wrapper">
                    <select name="condition" class="form-select">
                        <option value="">選択してください</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}" {{ old('condition_id')==$condition->id ? 'selected' : '' }}>{{$condition->condition }}</option>
                        @endforeach
                    </select>
                    @error('condition')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                     @enderror
                </div>
            </div>
        </section>

        {{-- 商品名と説明 --}}
        <section class="form-section">
            <h2 class="section-title">商品名と説明</h2>

            {{-- 商品名 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品名</h3>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}">
            </div>
            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            {{-- ブランド名 --}}
            <div class="form-group">
                <h3 class="subsection-title">ブランド名</h3>
                <input type="text" name="brand" class="form-input" value="{{ old('brand') }}" >
            </div>

            {{-- 商品の説明 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品の説明</h3>
                <textarea name="explanation" class="form-textarea">{{ old('explanation') }}</textarea>
            </div>
            @error('explanation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </section>

        {{-- 販売価格 --}}
        <section class="form-section price-section">
            <h2 class="section-title">販売価格</h2>
            <div class="form-group price-input-wrapper">
                <span class="currency-symbol">¥</span>
                <input type="number" name="price" class="form-input price-input" value="{{ old('price') }}" placeholder="0">
            </div>
            @error('price')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </section>

        <div class="submit-area">
            <button type="submit" class="submit-button-large">出品する</button>
        </div>

    </form>
</div>
@endsection