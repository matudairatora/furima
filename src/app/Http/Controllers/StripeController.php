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
    public function createPaymentIntent(Request $request, $itemId)
    {
        
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            
            $itemModel = Item::findOrFail($itemId);
            $price = $itemModel->price; 
            
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $price, 
                'currency' => 'jpy',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => ['item_id' => $itemId], 
            ]);
            
            
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Creation Failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Payment Intentの作成中にエラーが発生しました。'
            ], 500);
        }
    }

    
    public function checkout($itemId)
    {
        return view('checkout', ['itemId' => $itemId]);
    }

    
    public function completePayment(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus = $request->query('redirect_status');
        $itemId = $request->query('item_id');
        
        Stripe::setApiKey(config('services.stripe.secret'));

        
        if ($redirectStatus !== 'succeeded' || !$paymentIntentId || !$itemId) {
            return redirect()->route('auth.index')->with('error', '支払いは完了しませんでした。ステータス: ' . $redirectStatus);
        }
        
        try {
            
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $item = Item::findOrFail($itemId); 

            if ($paymentIntent->status === 'succeeded') {
                
                if (!$item->is_sold) {
                    $item->buyer_id = Auth::id();
                    $item->is_sold = true;
                    $item->save();
                    
                    return redirect()->route('auth.index')->with('success', '商品の購入が完了しました。');
                }

                return redirect()->route('auth.index')->with('info', 'この商品は既に購入済みでした。');

            } else {
                return redirect()->route('auth.index')->with('error', '支払いは完了しませんでした。再度お試しください。');
            }

        } catch (\Exception $e) {
            Log::error('Payment Completion Failed: ' . $e->getMessage());
            return redirect()->route('auth.index')->with('error', '決済処理中にエラーが発生しました。');
        }
    }
}

