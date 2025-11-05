<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
        public function index()
{
  return view('auth.index');
}
    
public function edit(Request $request){
       
        // ★致命的な問題の修正: ここで profile_setup_completed を true にする処理を削除★
        
        // ★ビューの修正: profile.blade.php に対応するビューを返す★
        return view('auth.profile'); 
        // もしビューの階層が auth/profile.blade.php なら return view('auth.profile');
    }
    
    // 【修正2】update メソッド: データ保存と同時にフラグを更新する
    public function update(Request $request)
    {
        $user = Auth::user();

        // =========================================================
        // ★修正1: usersテーブル（ユーザー名とフラグ）の更新処理
        // =========================================================
        $dataToUpdateUser = [
            'name' => $request->username, // usersテーブルの name カラムを更新
        ];
        
        // 初回設定完了の場合のみ、完了フラグをセット
        if (!$user->profile_setup_completed) {
            $dataToUpdateUser['profile_setup_completed'] = true;
        }

        $user->update($dataToUpdateUser);

        // =========================================================
        // ★修正2: mypageテーブル（住所情報）の保存/更新処理
        // =========================================================
        $dataToUpdateMypage = [
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building_name' => $request->building_name, // mypageテーブルのカラム名に一致
        ];
        
        // mypage レコードが存在すれば更新、存在しなければ user_id をつけて新規作成
        // $user->mypage() は User モデルで定義したリレーションを利用
        $user->mypage()->updateOrCreate(
            ['user_id' => $user->id], // 検索条件（この user_id のレコードを探す）
            $dataToUpdateMypage         // 更新/作成するデータ
        );

        return redirect()->route('auth.index')->with('success', 'プロフィールが更新されました。');
    }
}