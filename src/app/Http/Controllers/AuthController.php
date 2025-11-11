<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
   public function item(Request $request){
       
    $conditions = Condition::all();
    $categories = Category::all();

        return view('auth.item',compact('conditions','categories'));  
    }
    public function item_create(Request $request){
    
        $imagePath = $request->file('image')->store('public/images/item');
        $imageUrl = Storage::url($imagePath);

        $itemData = $request->only([
        'name',
        'price',
        'brand',
        'explanation',
        ]);
        // フォームからの condition の値を condition_id として設定
        $itemData['condition_id'] = $request->input('condition'); 
        // ログインユーザーのIDを設定（認証済みであることが前提）
        $itemData['user_id'] = Auth::id(); 
        // 保存した画像のパスを追加
        $itemData['image'] = $imageUrl; 
        
        // item モデルにリレーションのためのメソッド（categories()など）が定義されている必要があります。
        
        // 4. itemレコードの作成
        $item = Item::create($itemData);

        // 5. カテゴリー（多対多）の保存
        // 'categories[]' で送られてきたIDの配列を取得し、リレーションの中間テーブルに保存 (sync)
        $item->categories()->sync($request->input('categories'));
        // 6. 完了後のリダイレクト (ここでは仮に index に戻る)
        return redirect()->route('auth.index');
    }
}
