<?php

namespace App\Http\Controllers;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Stripe\PaymentIntent;


class StripeController extends Controller
{
public function createPaymentIntent(Request $request,$itemId)
{
   // 1. Stripeクライアントの初期化
    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        // 2. データベースから正確な商品情報を取得
        // $itemId を引数から受け取り、使用します
        $itemModel = Item::findOrFail($itemId);
        $price = $itemModel->price; 
        
        // 3. Payment Intentを作成
        $paymentIntent = PaymentIntent::create([
            // 金額はデータベースから取得した $price を使用
            'amount' => $price, 
            'currency' => 'jpy',
            // Payment Elementに必要な設定
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            // 注文情報を管理するためのメタデータ（任意）
            'metadata' => ['item_id' => $itemId], 
        ]);
        
        // 4. クライアント側が期待する clientSecret を JSON 形式で返す
        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);

    } catch (\Exception $e) {
        // エラーログを記録
        Log::error('Stripe Payment Intent Creation Failed: ' . $e->getMessage());
        
        // クライアントにエラーメッセージを返す
        return response()->json([
            'error' => 'Payment Intentの作成中にエラーが発生しました。'
        ], 500);
    }
}

    /**
     * チェックアウト画面 (checkout.blade.phpを表示)
     */
    public function checkout($itemId)
    {
        return view('checkout', ['itemId' => $itemId]);
    }
    
    public function completePurchase(Request $request)
    {
        // ★ 認証チェックを追加：購入者情報を記録するために必須
        if (!Auth::check()) {
             return redirect()->route('auth.index')->with('error', '購入を完了するにはログインが必要です。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $sessionId = $request->get('session_id');
        $itemId = $request->get('item_id'); // return_urlからitemIdを取得

        if (!$sessionId || !$itemId) {
            return redirect()->route('auth.index')->with('error', '決済情報が不足しています。');
        }

        try {
            $session = Session::retrieve($sessionId);
            $item = Item::findOrFail($itemId); // 商品が存在するか確認

            if ($session->status === 'complete' && $session->payment_status === 'paid') {
                // 支払い成功！
                
                // データベースの注文を更新 (商品ソールドアウト処理)
                if (!$item->is_sold) {
                    $item->buyer_id = Auth::id(); // 認証ユーザーを購買者として設定
                    $item->is_sold = true;
                    $item->save();
                    
                    // 成功メッセージと共に '/' へリダイレクト
                    return redirect()->route('auth.index')->with('success', '商品の購入が完了しました。');
                }

                // 既に売却済みだった場合も成功とみなしてトップページへリダイレクト
                return redirect()->route('auth.index')->with('info', 'この商品は既に購入済みでした。');

            } else {
                // 支払い保留中または失敗
                return redirect()->route('auth.index')->with('error', '支払いは完了しませんでした。再度お試しください。');
            }

        } catch (\Exception $e) {
            Log::error('Payment Completion Failed: ' . $e->getMessage());
            // エラーが発生した場合もトップページへ
            return redirect()->route('auth.index')->with('error', '決済処理中にエラーが発生しました。');
        }
    }
   
}
