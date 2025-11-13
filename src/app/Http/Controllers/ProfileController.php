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
public function index(Request $request)
{
   $tab = $request->query('tab', 'recommend');
        $items = collect(); // 商品リストの初期化

        // ログインしていて、'tab'が'mylist'の場合のみお気に入り商品を取得
        if ($tab === 'mylist' && Auth::check()) {
            $user = Auth::user();
            // 認証ユーザーのお気に入りリレーションを通じて商品を取得
            $items = $user->favorites()->latest()->get(); 
            
        } else { 
            // 'recommend'（おすすめ）またはログインしていない場合、全ての商品を取得
            $items = Item::latest()->get();
        }

        // ビューに現在のタブの状態と商品リストを渡す
        return view('auth.index', [
            'items' => $items,
            'currentTab' => $tab, 
        ]);
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

    public function mypage(Request $request)
    {
        $user = Auth::user();
       // クエリパラメータ 'page' を取得。指定がなければ 'sell' をデフォルトとする
        $page = $request->query('page', 'sell');

        // 商品データを取得するロジックを分岐
        if ($page === 'buy') {
            // ★ 購入した商品を取得 ★
            // Itemモデルの buyer_id が現在のユーザーIDと一致するものを取得
            $items = Item::where('buyer_id', $user->id)
                         ->latest()
                         ->get();

        } elseif($page === 'sell') { // 'sell' またはその他の値の場合
            // ★ 出品した商品を取得 (デフォルトの挙動) ★
            // Itemモデルの user_id が現在のユーザーIDと一致するものを取得
            $items = Item::where('user_id', $user->id)
                         ->latest()
                         ->get();
        }
        
    return view('auth.mypage', [
            'user' => $user,
            'items' => $items, 
        ]);
    }
    



}