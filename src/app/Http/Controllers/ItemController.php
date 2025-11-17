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
use App\Models\ShippingAddress;

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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($itemId);
        $user = Auth::user();

        // ★ 修正: まず「この商品用の配送先」が保存されているか確認
        $shipping = ShippingAddress::where('user_id', $user->id)->where('item_id', $itemId)->first();

        // 配送先情報
        // shipping_addresses にデータがあればそれを優先、なければ mypage を使用
        if ($shipping) {
            $userAddress = [
                'postcode' => $shipping->postcode,
                'address_line' => $shipping->address . ($shipping->building ? ' ' . $shipping->building : ''),
            ];
        } elseif ($user->mypage) {
            $userAddress = [
                'postcode' => $user->mypage->postcode,
                'address_line' => $user->mypage->address . ($user->mypage->building ? ' ' . $user->mypage->building : ''),
            ];
        } else {
             $userAddress = [
                'postcode' => '',
                'address_line' => '',
            ];
        }

        return view('auth.purchase', [
            'item' => $item,
            'userAddress' => $userAddress,
        ]);
    }


   public function showAddressEditForm(Request $request)
    {
        $itemId = $request->query('itemId');
        $user = Auth::user();

        // ★ 修正: 初期値を決定するロジック
        // item_id があり、かつ既に配送先設定があるならそれをロード
        $address = null;
        if ($itemId) {
            $address = ShippingAddress::where('user_id', $user->id)->where('item_id', $itemId)->first();
        }
        
        // なければプロフィールの住所を使用
        if (!$address) {
            $address = $user->mypage; 
        }

        return view('auth.address_edit', [
            'address' => $address, // $user ではなく $address を渡すように変更
            'itemId' => $itemId,
        ]);
    }

public function updateAddress(AddressRequest $request)
    {
        $user = Auth::user();
        $itemId = $request->input('item_id');
        $updateData = $request->validated();
        $updateData['building'] = $request->input('building');

        // ★ 修正: itemId がある場合は「配送先テーブル」を保存/更新
        if ($itemId) {
            ShippingAddress::updateOrCreate(
                ['user_id' => $user->id, 'item_id' => $itemId],
                $updateData
            );
            
            return redirect()->route('item.purchase', ['itemId' => $itemId]);
        }

        // itemId がない場合（プロフィール編集など）は従来の処理
        $mypage = $user->mypage;
        if (is_null($mypage)) {
            $user->mypage()->create($updateData);
        } else {
            $mypage->fill($updateData)->save();
        }

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
