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
*/Route::middleware('auth','profile_check')->group(function () {
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
 Route::get('/', [ProfileController::class, 'index'])->name('auth.index');
 
 Route::get('/item/edit', [AuthController::class, 'item'])->name('auth.item');
 Route::post('/item', [AuthController::class, 'item_create'])->name('item.create');

Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');
Route::post('/item/{item_id}/comment', [ItemController::class, 'addComment'])->name('item.comment');


});


