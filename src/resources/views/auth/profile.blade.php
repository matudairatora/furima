@extends('layouts.app') {{-- app.blade.phpを継承する場合（プロジェクトの構造に合わせて適宜変更してください） --}}

@section('title', 'プロフィール設定')

@section('css')
<link href="{{ asset('css/profile.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="profile-container">
    <h2 class="profile-title">プロフィール設定</h2>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf {{-- CSRF対策 --}}
        
        <div class="profile-content">
            {{-- 画像アップロードエリア --}}
            <div class="image-upload-area">
                {{-- 修正: 現在の画像を表示 --}}
                <img id="current_mypage" 
                     src="{{ asset($user->mypage->mypage ?? 'placeholder-image-path') }}" 
                     alt="プロフィール画像"
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                
                <label for="mypage" class="image-select-button">
                    画像を<br>選択する
                </label>
                {{-- 実際には非表示のinput[type="file"] --}}
                <input type="file" id="mypage" name="mypage" style="display:none;" onchange="previewImage(event);">
            </div>

            {{-- フォーム項目 --}}
            <div class="form-group">
                <label for="name">ユーザー名</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name ?? '')}}">
            </div>

            <div class="form-group">
                <label for="postcode">郵便番号</label>
                <input type="text" id="postcode" name="postcode" class="form-input" value="{{ old('postcode', $user->mypage->postcode ?? '')}}">
            </div>

            <div class="form-group">
                <label for="address">住所</label>
                <input type="text" id="address" name="address" class="form-input" value="{{ old('address', $user->mypage->address ?? '')}}">
            </div>

            <div class="form-group">
                <label for="building">建物名</label>
                <input type="text" id="building" name="building" class="form-input" value="{{ old('building', $user->mypage->building ?? '')}}">
            </div>

            <div class="form-action">
                <button type="submit" class="update-button">更新する</button>
            </div>
        </div>
    </form>
</div>
@endsection
@section('js')
<script>
    // 画像選択時にプレビューを表示する関数
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('current_mypage');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection