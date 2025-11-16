<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Condition;
use App\Models\Category;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;

class ItemController extends Controller
{
   public function show($id){

    
    $item = Item::with(['comments.user', 'condition', 'categories','favorites'])
                ->findOrFail($id);
    return view('auth.itemsell', compact('item'));
   } 
   
   public function addComment(CommentRequest $request, $id)
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
                'postcode' => '',
                'address_line' => '',
            ];
        }

        return view('auth.purchase', [
            'item' => $item, 
            'userAddress' => $userAddress, // 配送先情報をビューに渡す
        ]);
    }


    public function showAddressEditForm(Request $request)
{
    
    // itemIdをクエリパラメータから取得（例: /address/edit?itemId=123）
        $itemId = $request->query('itemId');
    // 認証ユーザーのマイページ情報を取得
    $user = Auth::user()->load('mypage');
    
    // 住所変更画面のビューを返す
    return view('auth.address_edit', [
        'user' => $user,'itemId' => $itemId,
    ]);
}

// 住所情報を更新するメソッド
public function updateAddress(AddressRequest $request)
{
    // 1. 認証ユーザーを取得
   $user = Auth::user();
    
    // 1. 既存の Mypage モデルを取得 (存在しなければ null)
    $mypage = $user->mypage; 

    // リクエストから更新データを配列で取得
    // building は AddressRequest にはないため、個別に追加
    $updateData = $request->validated();
    $updateData['building'] = $request->input('building');
    
    if (is_null($mypage)) {
        // 2. mypage が null の場合（初回登録）、新規作成する
        // create() に $updateData を渡すことで、NOT NULL 制約のエラーを回避
        $user->mypage()->create($updateData); 
        
    } else {
        // 3. mypage が存在する場合、データを更新し保存する
        // fill() でデータを一括設定し、save() で保存
        $mypage->fill($updateData)->save();
    }
    // -----------------------------------------------------------------
        // ★ 修正点: リダイレクト処理で item_id を取得し利用する ★
        // -----------------------------------------------------------------
        
        // address_edit.blade.php から hidden input で渡された item_id を取得
        $itemId = $request->input('item_id'); 
        
        // itemId が取得できたら購入画面へリダイレクト
        if ($itemId) {
            return redirect()->route('item.purchase', ['itemId' => $itemId]);
        }

        // itemId が取得できなかった場合のフォールバック処理 (例: マイページ)
        return redirect()->route('auth.mypage');
    
}
    public function processPurchase(PurchaseRequest $request, $itemId)
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
                       // データベースに保存

        


        // プルダウンで選択された値を取得
        $paymentMethod = $request->input('payment_method');
        
        switch ($paymentMethod) {
            case 'convenience':
                // '1'が選択されたら商品一覧ページへ
                $item->buyer_id = Auth::id(); // 認証ユーザーを購買者として設定
                $item->is_sold = true;        // ソールドアウトに設定
                $item->save(); 
                return redirect()->route('auth.index');
            case 'card':
                // '2'が選択されたらユーザープロフィールページへ
                //return redirect()->route('auth.index');
                return redirect()->route('checkout', [
                    'itemId' => $item->id,
                ]);
            
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
