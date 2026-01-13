<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Rating;   
use App\Models\Message;  
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

    public function edit(Request $request)
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
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

        if ($request->hasFile('mypage_image')) {
            $path = $request->file('mypage_image')->store('profile_images', 'public');
            $dataToUpdateMypage['mypage_image'] = $path;
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

        
        $averageRating = 0;
        if (class_exists(Rating::class)) {
            $averageRating = Rating::where('user_id', $user->id)->avg('rating');
        }

        
        $tradingItemsQuery = Item::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
        })
        ->whereNotNull('buyer_id')
        ->where('is_completed', false);

        
        $allTradingItems = $tradingItemsQuery->get();
        
        $totalUnread = 0;
        if (class_exists(Message::class)) {
            foreach ($allTradingItems as $tItem) {
                // 各アイテムごとの未読数
                $count = Message::where('item_id', $tItem->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();
                
                
                $tItem->unread_count = $count;
                
                // 合計に加算
                $totalUnread += $count;
            }
        }

        // 3. 表示するアイテムリストの切り替え
        $items = collect(); 

        if ($page === 'buy') {
            // 購入した商品
            $items = Item::where('buyer_id', $user->id)
                ->latest()
                ->get();

        } elseif ($page === 'sell') {
            // 出品した商品
            $items = Item::where('user_id', $user->id)
                ->latest()
                ->get();

        } elseif ($page === 'trading') {
            // 取引中の商品
            $items = $allTradingItems;
        }

        return view('auth.mypage', [
            'user' => $user,
            'items' => $items,
            'averageRating' => $averageRating,
            'totalUnread' => $totalUnread, 
        ]);
    }
}