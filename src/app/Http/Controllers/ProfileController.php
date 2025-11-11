<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mypage;
use App\Models\Item;
use App\Models\User;

class ProfileController extends Controller
{
public function index()
{
    $items = Item::all();
  return view('auth.index',compact('items',));
}
    
public function edit(Request $request){
       
        $user = Auth::user();
        
        
        return view('auth.profile',compact('user')); 
        
    }
    
 
    public function update(Request $request)
    {
        $user = Auth::user();

        $dataToUpdateUser = [
            'name' => $request->name, 
        ];
        
        // 初回設定完了の場合のみ、完了フラグをセット
        if (!$user->profile_setup_completed) {
            $dataToUpdateUser['profile_setup_completed'] = true;
        }

        $user->update($dataToUpdateUser);

        $dataToUpdateMypage = [
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building' => $request->building, 
        ];
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
        // 画像をストレージに保存（例：storage/app/public/profiles フォルダ）
        // storeメソッドは保存先のパスを返します
        $path = $request->file('profile_image')->store('public/profiles'); 
        
        // asset()ヘルパーでアクセスできるように 'storage/' から始まる公開パスに変換
        $profileImagePath = str_replace('public/', 'storage/', $path);
        
        // Mypageデータに画像パスを追加（このキー名がデータベースのカラム名と一致する必要があります）
        $dataToUpdateMypage['profile_image'] = $profileImagePath;
    }
       
        $user->mypage()->updateOrCreate(
            ['user_id' => $user->id], 
            $dataToUpdateMypage         
        );

        return redirect()->route('auth.index')->with('success', 'プロフィールが更新されました。');
    }

    public function mypage()
    {
        $user = Auth::user();
        $items = Item::all();
        
    return view('auth.mypage',compact('user','items',));
    }
    
}