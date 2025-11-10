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
}
