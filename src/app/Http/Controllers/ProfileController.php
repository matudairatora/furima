<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mypage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
public function index(Request $request)
{
   $tab = $request->query('tab', 'recommend');
    $keyword = $request->input('keyword'); 
    
    $query = Item::query(); 

if (Auth::check()) {
        $query->where('user_id', '!=', Auth::id());
    }

    
    if ($tab === 'mylist') {
        if (Auth::check()) {
            $user = Auth::user();
            $query->whereHas('favorites', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } else {           
            $query->whereRaw('1 = 0');
        }
    }
    
    if (!empty($keyword)) {
        
        $query->where('name', 'LIKE', '%' . $keyword . '%'); 
    }

   
    $items = $query->latest()->get(); 

    
    return view('auth.index', [
        'items' => $items,
        'currentTab' => $tab, 
        'keyword' => $keyword, 
    ]);
}
    
public function edit(Request $request){
       
        $user = Auth::user();
        
        
        return view('auth.profile',compact('user')); 
        
    }
    
 
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        $dataToUpdateUser = [
            'name' => $request->name, 
        ];
        
        
        

        $user->update($dataToUpdateUser);

        $dataToUpdateMypage = [
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building' => $request->building, 
        ];
        
        if ($request->hasFile('mypageimage')) {
            
            $path = $request->file('mypageimage')->store('profile_images', 'public'); 
            
           
            $dataToUpdateMypage['mypageimage'] = $path;
        }

       
        $user->mypage()->updateOrCreate(
            ['user_id' => $user->id], 
            $dataToUpdateMypage         
        );

        return redirect()->route('auth.index')->with('success', 'プロフィールが更新されました。');
    }

    public function mypage(Request $request)
    {
        $user = Auth::user();
       
        $page = $request->query('page', 'sell');

        
        if ($page === 'buy') {
           
            $items = Item::where('buyer_id', $user->id)
                         ->latest()
                         ->get();

        } elseif($page === 'sell') { 
            $items = Item::where('user_id', $user->id)
                         ->latest()
                         ->get();
        }
        
    return view('auth.mypage', [
            'user' => $user,
            'items' => $items, 
        ]);

    }


}