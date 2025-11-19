<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;


class AuthController extends Controller
{
   public function item(Request $request){
       
    $conditions = Condition::all();
    $categories = Category::all();

        return view('auth.item',compact('conditions','categories'));  
    }
    public function item_create(ExhibitionRequest $request){
    
        $imagePath = $request->file('image')->store('images/item', 'public');

        $itemData = $request->only([
        'name',
        'price',
        'brand',
        'explanation',
        ]);
        
        $itemData['condition_id'] = $request->input('condition'); 
        
        $itemData['user_id'] = Auth::id(); 
       
        $itemData['image'] = $imagePath;
        
        
        
        
        $item = Item::create($itemData);

        
        $item->categories()->sync($request->input('categories'));
        
        return redirect()->route('auth.index');
    }
}
