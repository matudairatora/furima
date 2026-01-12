<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Rating;   // 追加: 評価モデル
use App\Models\Message;  // 追加: メッセージモデル
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'recommend');
        $keyword = $request->input('keyword');

        $query = Item::query();

        // ログインしている場合、自分の商品は除外
        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        // マイリスト（お気に入り）タブの処理
        if ($tab === 'mylist') {
            if (Auth::check()) {
                $user = Auth::user();
                $query->whereHas('favorites', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } else {
                // ログインしていない場合は何も表示しない
                $query->whereRaw('1 = 0');
            }
        }

        // キーワード検索
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        // 出品完了していないものだけを表示する場合（任意）
        // $query->whereNotNull('buyer_id'); // 売り切れを除外したい場合はここを調整

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

        // 1. ユーザー評価の平均値
        $averageRating = 0;
        if (class_exists(Rating::class)) {
            $averageRating = Rating::where('user_id', $user->id)->avg('rating');
        }

        // 2. 「取引中」の全アイテムを取得して、未読メッセージの合計を算出 (タブのバッジ用)
        // ※現在のページが何であれ、タブのバッジ用に計算が必要です
        $tradingItemsQuery = Item::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
        })
        ->whereNotNull('buyer_id')
        ->where('is_completed', false);

        // クエリの結果を取得（複製して使うため get() しておく）
        $allTradingItems = $tradingItemsQuery->get();
        
        $totalUnread = 0;
        if (class_exists(Message::class)) {
            foreach ($allTradingItems as $tItem) {
                // 各アイテムごとの未読数
                $count = Message::where('item_id', $tItem->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();
                
                // アイテムオブジェクトにプロパティとして一時保存（tradingタブ表示時に使用）
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
            // 先ほど計算済みのコレクションを使用（未読数プロパティ付き）
            $items = $allTradingItems;
        }

        return view('auth.mypage', [
            'user' => $user,
            'items' => $items,
            'averageRating' => $averageRating,
            'totalUnread' => $totalUnread, // ★追加: タブ表示用の合計未読数
        ]);
    }
}