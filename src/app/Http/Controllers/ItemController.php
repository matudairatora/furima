<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Condition;
use App\Models\Category;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class ItemController extends Controller
{
   public function show($id){

    
    $item = Item::with(['comments.user', 'condition', 'categories','favorites'])
                ->findOrFail($id);
    return view('auth.itemsell', compact('item'));
   } 
   
   public function addComment(Request $request, $id)
    {
        $commentData = [
            
            'item_id' => $id, 
            'user_id' => Auth::id(),
            'comment' => $request->input('comment'), 
        ];

        
        $comment = Comment::create($commentData);

        return redirect()->route('item.show',['item_id' => $id]);
    }

    public function toggleFavorite(Request $request, $itemId)
    {
        $user = Auth::user();
        $item = Item::findOrFail($itemId);

        // 多対多リレーションのtoggle()メソッドでお気に入り登録/解除を自動で切り替え
        $user->favorites()->toggle($item->id);

        return back(); // 前のページに戻る
    }

public function showPurchaseForm($itemId)
    {
        // 認証ユーザーのチェック
        if (!Auth::check()) {
            return redirect()->route('login'); // ログインページにリダイレクト
        }

        // 商品IDに基づいてデータを取得
        $item = Item::findOrFail($itemId); 
        
        // ★ 認証ユーザーのマイページ情報を取得 ★
        // Userモデルにmypageリレーションが定義されていることを前提とします
        $userMypage = Auth::user()->mypage;

        // 配送先情報
        $userAddress = null;
        if ($userMypage) {
            $userAddress = [
                // mypageテーブルのカラム名に合わせてキーを設定
                'postcode' => $userMypage->postcode,
                // addressとbuildingを結合して表示用のアドレスを作成
                'address_line' => $userMypage->address . ($userMypage->building ? ' ' . $userMypage->building : ''),
            ];
        } else {
             // マイページ情報がない場合のデフォルト値 (任意)
             $userAddress = [
                'postcode' => '未登録',
                'address_line' => '配送先情報が登録されていません。',
            ];
        }

        return view('auth.purchase', [
            'item' => $item, 
            'userAddress' => $userAddress, // 配送先情報をビューに渡す
        ]);
    }

    public function processPurchase(Request $request, $itemId)
    {   
        // 1. 認証チェック
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        // 2. 商品を取得
        $item = Item::findOrFail($itemId);

        // 3. 既にソールドアウトでないか確認 (任意: 二重購入防止)
        if ($item->is_sold) {
            return back()->with('error', 'この商品は既に売り切れました。');
        }

        // 4. 購入者IDとソールドアウトフラグを設定
        $item->buyer_id = Auth::id(); // 認証ユーザーを購買者として設定
        $item->is_sold = true;        // ソールドアウトに設定
        $item->save();                // データベースに保存

        


        // プルダウンで選択された値を取得
        $paymentMethod = $request->input('payment_method');
        
        switch ($paymentMethod) {
            case 'convenience':
                // '1'が選択されたら商品一覧ページへ
                return redirect()->route('auth.index');
            case 'card':
                // '2'が選択されたらユーザープロフィールページへ
                return redirect()->route('auth.index');
                //カードルート完成版
                //return redirect()->route('item.aftercard');
            
            default:
                // 予期しない値の場合は、トップページなどにリダイレクト
                return redirect()->route('auth.index');
        }
    }
    
    public function afterpurchasecard()
    {
        // カード払い完了後の処理やビューの表示
        return view('item.aftercard');
    }

}
