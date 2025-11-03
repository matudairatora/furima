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

        // 1. バリデーションとデータ保存処理を追加
        $request->validate([
            'username' => 'required|string|max:255',
            'postcode' => 'required|string|max:8',
            'address' => 'required|string|max:255',
            // 画像やその他の項目もここに追加
        ]);

        // 2. ユーザー情報（またはプロフィールテーブル）を更新
        $dataToUpdate = [
            'username' => $request->username,
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building_name' => $request->building_name,
        ];
        
        // 初回設定完了の場合のみ、完了フラグをセット
        if (!$user->profile_setup_completed) {
            $dataToUpdate['profile_setup_completed'] = true;
        }

        $user->update($dataToUpdate);

        return redirect()->route('auth.index')->with('success', 'プロフィールが更新されました。');
    }
}