<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Condition;
use App\Models\Category;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class ItemController extends Controller
{
   public function show($item_id){
    $item = Item::findOrFail($item_id);
   $comments = Comment::where('item_id', $item_id)->get();
    return view('auth.itemsell', compact('item', 'comments'));
   } 
   
   public function addComment(Request $request, $item_id)
    {
        
        return redirect()->route('auth.itemsell', ['item_id' => $item_id]);
    }
}
