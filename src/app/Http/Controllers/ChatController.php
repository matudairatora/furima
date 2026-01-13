<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; 
use App\Mail\TransactionCompletedEmail; 
use App\Models\Item;
use App\Models\Message;
use App\Models\Rating;

class ChatController extends Controller
{
   public function show($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        if ($item->user_id !== $user->id && $item->buyer_id !== $user->id) {
            abort(403, 'この取引にアクセスする権限がありません。');
        }

        $partner = ($user->id === $item->user_id) 
            ? $item->buyer 
            : $item->user;

        
        $messages = Message::where('item_id', $item_id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        if (\Schema::hasColumn('messages', 'read_at')) {
            Message::where('item_id', $item_id)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

       
        $chat_items = Item::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('buyer_id', $user->id);
            })
            ->whereNotNull('buyer_id')
            ->where('id', '!=', $item_id)
            ->addSelect(['latest_message_time' => Message::select('created_at')
                ->whereColumn('item_id', 'items.id')
                ->latest()
                ->limit(1)
            ])
            ->orderByDesc('latest_message_time')
            ->orderByDesc('created_at')
            ->get();

        
        $hasRated = Rating::where('item_id', $item_id)
                          ->where('rater_id', $user->id)
                          ->exists();

        
        $partnerId = ($user->id === $item->user_id) ? $item->buyer_id : $item->user_id;
        $partnerHasRated = Rating::where('item_id', $item_id)
                          ->where('rater_id', $partnerId)
                          ->exists();

        
        return view('chat.show', compact('item', 'messages', 'partner', 'chat_items', 'hasRated', 'partnerHasRated', 'user'));
    }
    
    
    public function complete($item_id)
    {
        $item = Item::findOrFail($item_id);

        if (Auth::id() !== $item->user_id && Auth::id() !== $item->buyer_id) {
            abort(403);
        }

        
        
        return redirect()->route('chat.show', ['item_id' => $item_id])
            ->with('transaction_completed', true);
    }

    
    public function sendRating(Request $request, $item_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        
        // 二重投稿防止
        if (Rating::where('item_id', $item_id)->where('rater_id', $user->id)->exists()) {
             return redirect()->route('auth.mypage');
        }

        $targetUserId = ($user->id === $item->user_id) ? $item->buyer_id : $item->user_id;

        Rating::create([
            'user_id' => $targetUserId,
            'rater_id' => $user->id,
            'item_id' => $item->id,
            'rating' => $request->rating,
        ]);

        
        if ($user->id === $item->buyer_id) {
            // 出品者を取得
            $seller = $item->user; 
            
            // 出品者のメールアドレスがあれば送信
            if ($seller && $seller->email) {
                Mail::to($seller->email)->send(new TransactionCompletedEmail($item, $user));
            }
        }

        
        $ratingCount = Rating::where('item_id', $item->id)->count();

        if ($ratingCount >= 2) {
            $item->is_completed = true;
            $item->save();
        }

        return redirect()->route('auth.index')->with('success', '評価を送信しました！');
    }
    
    
    public function store(Request $request, $item_id)
    {
        $request->validate([
            'content' => 'required|max:400',
            'image' => 'nullable|image|mimes:jpeg,png',
        ], [
            'content.max' => '本文は400文字以内で入力してください',
            'content.required' => '本文を入力してください',
            'image.image' => '「.png」または「.jpeg」形式でアップロードしてください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ]);

        $message = new Message();
        $message->user_id = Auth::id();
        $message->item_id = $item_id;
        $message->content = $request->input('content');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $message->image = $path;
        }

        $message->save();

        return redirect()->route('chat.show', ['item_id' => $item_id]);
    }
    public function destroyMessage($message_id)
    {
        $message = Message::findOrFail($message_id);

        // 自分のメッセージでなければエラー
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return back(); // 元の画面に戻る
    }

    // ★追加: メッセージ更新処理
    public function updateMessage(Request $request, $message_id)
    {
        $request->validate([
            'content' => 'required|max:400',
            'image' => 'nullable|image|mimes:jpeg,png',
        ], [
            'content.max' => '本文は400文字以内で入力してください',
            'content.required' => '本文を入力してください',
            'image.image' => '「.png」または「.jpeg」形式でアップロードしてください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ]);

        $message = Message::findOrFail($message_id);

        // 自分のメッセージでなければエラー
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->content = $request->input('content');
        $message->save();

        return back(); // 元の画面に戻る
    }
}