<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
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

Route::middleware('auth','profile_check')->group(function () {
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');


});
Route::middleware('auth')->group(function () {
 
Route::get('/item/edit', [AuthController::class, 'item'])->name('auth.item');
Route::post('/item', [AuthController::class, 'item_create'])->name('item.create');

Route::post('/item/{item_id}/comment', [ItemController::class, 'addComment'])->name('item.comment');
Route::post('/item/{itemId}/favorite', [ItemController::class, 'toggleFavorite'])->name('item.toggle_favorite');

Route::get('/mypage', [ProfileController::class, 'mypage'])->name('auth.mypage');
});


Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show'); // 商品詳細