<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StripeController;
use Illuminate\Http\Request;
use App\Models\Item;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ProfileController::class, 'index'])->name('auth.index'); // トップページ

Route::middleware('auth')->group(function () {


Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

 
Route::get('/item/edit', [AuthController::class, 'item'])->name('auth.item');
Route::post('/item', [AuthController::class, 'item_create'])->name('item.create');

Route::post('/item/{item_id}/comment', [ItemController::class, 'addComment'])->name('item.comment');
Route::post('/item/{itemId}/favorite', [ItemController::class, 'toggleFavorite'])->name('item.toggle_favorite');

Route::get('/mypage', [ProfileController::class, 'mypage'])->name('auth.mypage');


Route::get('/purchase/{itemId}', [ItemController::class, 'showPurchaseForm'])->name('item.purchase');


// 共通のPOSTルート（プルダウンの選択を受け取る）
Route::post('/purchase/{itemId}', [ItemController::class, 'processPurchase'])->name('item.process_purchase');

Route::get('/address/edit', [ItemController::class, 'showAddressEditForm'])->name('address.edit');
    
// 住所情報を更新するPOSTルート (新しいルート)
Route::post('/address', [ItemController::class, 'updateAddress'])->name('address.update');

//　検索フォーム
Route::get('/', [ProfileController::class, 'index'])->name('auth.index');


// クライアントサイドの支払いフォームを表示するページ (itemIdをクエリで受け取る)
Route::get('/checkout', function (Request $request) {
    // クエリパラメータからitemIdを取得
    $itemId = $request->query('itemId'); 
    
    // 実際に購入する商品を取得（必須）
    $item = \App\Models\Item::findOrFail($itemId);

    return view('checkout', [
        'stripePublicKey' => config('services.stripe.public'),
        'item' => $item, // ★ 商品情報をビューに渡す
    ]);
})->name('checkout'); // ★ name()ヘルパーを追加

// Checkout Sessionを作成するAPIエンドポイント
// POSTリクエストで商品IDを受け取れるように修正
Route::post('/create-payment-intent/{itemId}', [StripeController::class, 'createPaymentIntent'])->name('create-payment-intent');

// 決済完了後のリダイレクト先 (変更なし)
Route::get('/stripe/complete', [StripeController::class, 'completePurchase'])->name('stripe.complete');

});


Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show'); // 商品詳細