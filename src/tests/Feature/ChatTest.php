<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Message;
// use App\Models\SoldItem; // 削除
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionCompletedEmail;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // コンディションとカテゴリーのシーダー
        $this->seed(\Database\Seeders\ConditionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    /**
     * ヘルパー: 取引中の状態（商品が購入された状態）を作成する
     */
    private function createTradingItem($seller, $buyer)
    {
        $item = Item::factory()->create(['user_id' => $seller->id]);
        
        // itemsテーブルのbuyer_idを更新して「購入済み」とする
        $item->buyer_id = $buyer->id; 
        $item->save();

        return $item;
    }

    /**
     * FN001, FN002: マイページから取引チャット画面へ遷移できる
     */
    public function test_can_access_chat_page_from_mypage()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        // 1. マイページ（取引中タブ）にアクセス
        $response = $this->get(route('auth.mypage', ['page' => 'trading']));
        $response->assertStatus(200);
        $response->assertSee($item->name);

        // 2. チャット画面へアクセス
        $response = $this->get(route('chat.show', ['item_id' => $item->id]));
        $response->assertStatus(200);
        
        // ★修正: 画面表示に合わせて「」を追加
        $response->assertSee('「' . $seller->name . '」さんとの取引画面');
    }

    /**
     * FN003: 別取引遷移機能
     */
    public function test_can_navigate_to_another_transaction_from_sidebar()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        
        $item1 = $this->createTradingItem($seller, $buyer);
        $item2 = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        $response = $this->get(route('chat.show', ['item_id' => $item1->id]));
        $response->assertStatus(200);
        $response->assertSee($item2->name);
        
        $response = $this->get(route('chat.show', ['item_id' => $item2->id]));
        $response->assertStatus(200);
        $response->assertSee($item2->name);
    }

   /**
     * FN004: 取引自動ソート機能
     * 取引中の商品の並び順は、新規メッセージが来た順に表示する
     */
    public function test_trading_items_are_sorted_by_latest_message()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $this->actingAs($buyer);

        $currentItem = $this->createTradingItem($seller, $buyer);

        $itemOldest = $this->createTradingItem($seller, $buyer);
        $itemMiddle = $this->createTradingItem($seller, $buyer);
        $itemNewest = $this->createTradingItem($seller, $buyer);

        // ★修正: created_at を確実に反映させるため、個別にインスタンス化して保存
        
        // 1. 一番古いメッセージ (5日前)
        $msg1 = new Message();
        $msg1->fill([
            'item_id' => $itemOldest->id,
            'user_id' => $seller->id,
            'content' => 'Oldest message',
        ]);
        $msg1->created_at = now()->subDays(5);
        $msg1->save();

        // 2. 中くらいのメッセージ (3日前)
        $msg2 = new Message();
        $msg2->fill([
            'item_id' => $itemMiddle->id,
            'user_id' => $seller->id,
            'content' => 'Middle message',
        ]);
        $msg2->created_at = now()->subDays(3);
        $msg2->save();

        // 3. 最新のメッセージ (現在)
        $msg3 = new Message();
        $msg3->fill([
            'item_id' => $itemNewest->id,
            'user_id' => $seller->id,
            'content' => 'Newest message',
        ]);
        $msg3->created_at = now();
        $msg3->save();

        // 検証
        $response = $this->get(route('chat.show', ['item_id' => $currentItem->id]));

        $response->assertStatus(200);

        // サイドバーの商品名が「最新 → 中くらい → 古い」の順で並んでいるか確認
        $response->assertSeeInOrder([
            $itemNewest->name,
            $itemMiddle->name,
            $itemOldest->name
        ]);
    }

    /**
     * FN005: 新規通知マーク表示機能
     */
    public function test_displays_unread_message_count_badge()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        Message::create([
            'item_id' => $item->id,
            'user_id' => $seller->id,
            'content' => 'Hello Buyer!',
        ]);

        $this->actingAs($buyer);

        $response = $this->get(route('auth.mypage', ['page' => 'trading']));

        $response->assertStatus(200);
        $response->assertSee('1'); 
    }

    /**
     * FN005-2: 評価平均確認機能
     */
    public function test_fn005_2_displays_stars_based_on_rounded_average()
    {
        $user = User::factory()->create();
        $otherUser1 = User::factory()->create();
        $otherUser2 = User::factory()->create();

        $item1 = Item::factory()->create(['user_id' => $user->id]);
        $item2 = Item::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        // ケース1: 評価なし
        $response = $this->get(route('auth.mypage'));
        $response->assertStatus(200);
        $content = $response->getContent();
        $filledCount = substr_count($content, 'star filled');
        $this->assertEquals(0, $filledCount, '評価がないため、星は0個であるべき');

        // ケース2: 評価あり(平均3.5 -> 4)
        Rating::create([
            'item_id' => $item1->id,
            'rater_id' => $otherUser1->id,
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        Rating::create([
            'item_id' => $item2->id,
            'rater_id' => $otherUser2->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $response = $this->get(route('auth.mypage'));
        $response->assertStatus(200);
        $content = $response->getContent();
        $filledCount = substr_count($content, 'star filled');
        $this->assertEquals(4, $filledCount, '平均3.5(四捨五入で4)の場合、塗りつぶされた星は4つあるべき');
    }

    /**
     * FN006: チャット送信機能
     */
    public function test_can_send_message()
    {
        Storage::fake('public');
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        $data = [
            'content' => 'This is a test message.',
            'image' => UploadedFile::fake()->create('test.jpg', 100), 
        ];

        $response = $this->post(route('chat.store', ['item_id' => $item->id]), $data);

        $response->assertRedirect(route('chat.show', ['item_id' => $item->id]));

        $this->assertDatabaseHas('messages', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
            'content' => 'This is a test message.',
        ]);
    }

    /**
     * FN007, FN008: バリデーション
     */
    public function test_chat_validation_errors()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        // 1. 本文未入力
        $response = $this->post(route('chat.store', ['item_id' => $item->id]), [
            'content' => '',
        ]);
        $response->assertSessionHasErrors([
            'content' => '本文を入力してください'
        ]);

        // 2. 本文401文字以上
        $longText = str_repeat('a', 401);
        $response = $this->post(route('chat.store', ['item_id' => $item->id]), [
            'content' => $longText,
        ]);
        $response->assertSessionHasErrors([
            'content' => '本文は400文字以内で入力してください'
        ]);

        // 3. 画像形式エラー
        $response = $this->post(route('chat.store', ['item_id' => $item->id]), [
            'content' => 'ok',
            'image' => UploadedFile::fake()->create('test.gif'), 
        ]);
        $response->assertSessionHasErrors([
            'image' => '「.png」または「.jpeg」形式でアップロードしてください'
        ]);
    }

    /**
     * FN009: 入力情報保持機能
     */
    public function test_input_retention_on_validation_error()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        // ケース1: 画像エラー
        $validContent = 'This text should remain.';
        $response = $this->post(route('chat.store', ['item_id' => $item->id]), [
            'content' => $validContent,
            'image' => UploadedFile::fake()->create('test.gif'), 
        ]);
        $response->assertSessionHasErrors(['image']);
        $this->assertEquals($validContent, session()->get('_old_input')['content']);

        // ケース2: 文字数オーバー
        $longContent = str_repeat('a', 401);
        $response = $this->post(route('chat.store', ['item_id' => $item->id]), [
            'content' => $longContent,
        ]);
        $response->assertSessionHasErrors(['content']);
        $this->assertEquals($longContent, session()->get('_old_input')['content']);
    }

    /**
     * FN012, FN014: 取引完了評価機能（購入者）
     */
    public function test_buyer_can_complete_transaction_and_rate_seller()
    {
        Mail::fake(); 

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        $response = $this->post(route('review.store', ['item_id' => $item->id]), [
            'rating' => 5,
        ]);

        $response->assertRedirect(route('auth.index'));

        $this->assertDatabaseHas('ratings', [
            'item_id' => $item->id,
            'rater_id' => $buyer->id,
            'user_id' => $seller->id,
            'rating' => 5,
        ]);
    }

    /**
     * FN013: 取引後評価機能（出品者）
     */
    public function test_seller_can_rate_buyer_after_buyer_rates_seller()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        // 購入者が評価済み状態を作成
        Rating::create([
            'item_id' => $item->id,
            'rater_id' => $buyer->id,
            'user_id' => $seller->id,
            'rating' => 5,
        ]);

        $this->actingAs($seller);

        $response = $this->post(route('review.store', ['item_id' => $item->id]), [
            'rating' => 4,
        ]);

        $response->assertRedirect(route('auth.index'));

        $this->assertDatabaseHas('ratings', [
            'item_id' => $item->id,
            'rater_id' => $seller->id,
            'user_id' => $buyer->id,
            'rating' => 4,
        ]);

        // ★修正: 型キャストを追加 (MySQL/SQLiteの戻り値 `1` を true と比較するため)
        $this->assertTrue((bool)$item->fresh()->is_completed);
    }

    /**
     * FN016: メール送信機能
     */
    public function test_email_is_sent_to_seller_when_buyer_completes_transaction()
    {
        Mail::fake();

        $seller = User::factory()->create(['email' => 'seller@example.com']);
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);

        $this->actingAs($buyer);

        $this->post(route('review.store', ['item_id' => $item->id]), [
            'rating' => 5,
        ]);

        Mail::assertSent(TransactionCompletedEmail::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    }

    /**
     * FN010, FN011: メッセージの編集・削除機能
     */
    public function test_can_edit_and_delete_own_message()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createTradingItem($seller, $buyer);
        $this->actingAs($buyer);

        // メッセージ作成
        $message = Message::create([
            'item_id' => $item->id,
            'user_id' => $buyer->id,
            'content' => 'Original Content',
        ]);

        // --- 編集テスト (FN010) ---
        $response = $this->patch(route('chat.message.update', ['message_id' => $message->id]), [
            'content' => 'Updated Content',
        ]);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => 'Updated Content',
        ]);

        // --- 削除テスト (FN011) ---
        $response = $this->delete(route('chat.message.destroy', ['message_id' => $message->id]));

        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }
}