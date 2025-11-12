<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mypage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Storage;


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
        
        if ($request->hasFile('mypage')) {
            // 'public'ディスクの'profile_images'ディレクトリに画像を保存
            // putFileは一意なファイル名を自動生成し、パスを返します。
            $path = $request->file('mypage')->store('profile_images', 'public'); 
            
            // データベースに保存するパスをセット
            $dataToUpdateMypage['mypage'] = $path;
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