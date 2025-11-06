@extends('layouts.app') {{-- app.blade.php を継承 --}}

@section('css')
<link href="{{ asset('css/item.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="item-create-container">
    <h1 class="page-title">商品の出品</h1>

    <form action="/items" method="POST" enctype="multipart/form-data">
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
        </section>

        {{-- 商品の詳細 --}}
        <section class="form-section">
            <h2 class="section-title">商品の詳細</h2>

            {{-- カテゴリー --}}
            <div class="form-group">
                <h3 class="subsection-title">カテゴリー</h3>
                <div class="category-tags">
                    {{-- 画像にあるカテゴリーのタグを再現 --}}
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="ファッション"><span>ファッション</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="家電"><span>家電</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="インテリア"><span>インテリア</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="レディース"><span>レディース</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="メンズ"><span>メンズ</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="コスメ"><span>コスメ</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="本"><span>本</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="ゲーム"><span>ゲーム</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="スポーツ"><span>スポーツ</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="キッチン"><span>キッチン</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="ハンドメイド"><span>ハンドメイド</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="アクセサリー"><span>アクセサリー</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="おもちゃ"><span>おもちゃ</span></label>
                    <label class="tag-label"><input type="checkbox" name="categories[]" value="ベビー・キッズ"><span>ベビー・キッズ</span></label>
                </div>
            </div>

            {{-- 商品の状態 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品の状態</h3>
                <div class="select-wrapper">
                    <select name="condition" class="form-select">
                        <option value="">選択してください</option>
                        <option value="new">良好</option>
                        <option value="used_like_new">目立った汚れなし</option>
                        <option value="used_good">やや傷や汚れあり</option>
                        <option value="used_fair">状態が悪い</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- 商品名と説明 --}}
        <section class="form-section">
            <h2 class="section-title">商品名と説明</h2>

            {{-- 商品名 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品名</h3>
                {{-- old() ヘルパーを使用してエラー時に値を保持 (Laravelの慣例) --}}
                <input type="text" name="name" class="form-input" value="{{ old('name') }}">
            </div>

            {{-- ブランド名 --}}
            <div class="form-group">
                <h3 class="subsection-title">ブランド名</h3>
                <input type="text" name="brand" class="form-input" value="{{ old('brand') }}" placeholder="任意">
            </div>

            {{-- 商品の説明 --}}
            <div class="form-group">
                <h3 class="subsection-title">商品の説明</h3>
                <textarea name="explanation" class="form-textarea">{{ old('explanation') }}</textarea>
            </div>
        </section>

        {{-- 販売価格 --}}
        <section class="form-section price-section">
            <h2 class="section-title">販売価格</h2>
            <div class="form-group price-input-group">
                <h3 class="subsection-title">¥</h3>
                <input type="number" name="price" class="form-input price-input" value="{{ old('price') }}" placeholder="100">
            </div>
        </section>

        <div class="submit-area">
            <button type="submit" class="submit-button-large">出品する</button>
        </div>

    </form>
</div>
@endsection