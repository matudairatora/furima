<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mypage;

class ProfileController extends Controller
{
        public function index()
{
  return view('auth.index');
}
    
public function edit(Request $request){
       
        
        
        
        return view('auth.profile'); 
        
    }
    
 
    public function update(Request $request)
    {
        $user = Auth::user();

        $dataToUpdateUser = [
            'name' => $request->name, 
        ];
        
        // 初回設定完了の場合のみ、完了フラグをセット
        if (!$user->profile_setup_completed) {
            $dataToUpdateUser['profile_setup_completed'] = true;
        }

        $user->update($dataToUpdateUser);

        $dataToUpdateMypage = [
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building' => $request->building, 
        ];
        
       
        $user->mypage()->updateOrCreate(
            ['user_id' => $user->id], 
            $dataToUpdateMypage         
        );

        return redirect()->route('auth.index')->with('success', 'プロフィールが更新されました。');
    }
}