<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\item;
use App\Models\Condition;
use App\Models\Category;

class AuthController extends Controller
{
   public function item(Request $request){
       
    $conditions = Condition::all();
    $categories = Category::all();

        return view('auth.item',compact('conditions','categories'));  
    }
    public function item_create(Request $request){
    

        $item = $request->only([
        'name',
        'image',
        'price',
        'brand',
        'explanation',
        'condition_id',
        'coment_id',
        'favorite_id',
        'category_id',]);

        $conditionId = $request->input('condition');
        $contact['category_id'] = $new_category->id;
        item::create($item);
        return view('auth.index');
    }
}
