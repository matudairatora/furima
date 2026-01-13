<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ConditionSeeder::class); // コンディションが必要
        $this->seed(\Database\Seeders\CategorySeeder::class);  // カテゴリーが必要
    }

    /**
     * ID 8: いいね機能 - いいね（お気に入り）登録ができる
     */
    public function test_user_can_like_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // いいねボタンを押す（トグル処理へのPOST）
        $response = $this->post(route('item.toggle_favorite', ['itemId' => $item->id]));

        $response->assertStatus(302); // リダイレクト確認
        
        // 中間テーブルにレコードがあるか確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * ID 8: いいね機能 - 追加済みのアイコンは色が変化する
     */
    public function test_like_icon_is_colored_when_item_is_liked()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // Arrange: 先にお気に入り登録済みの状態を作っておく
        $user->favorites()->attach($item->id);

        // Act: 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        
        // Assert: アイコンのクラスが 'fas' (Solid:塗りつぶし) になっているか確認
        // ビューの実装に合わせて 'favorited' や 'fas fa-heart' などをチェック
        $response->assertSee('fas fa-heart');
    }

    /**
     * ID 8: いいね機能 - いいね解除ができる
     */
    public function test_user_can_unlike_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // 最初にお気に入り登録しておく
        $user->favorites()->attach($item->id);

        // もう一度押すと解除
        $this->post(route('item.toggle_favorite', ['itemId' => $item->id]));

        // データベースから消えているか確認
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * ID 5: マイリスト一覧取得 - いいねした商品だけが表示される
     */
    public function test_can_see_liked_items_in_mylist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // いいねした商品
        $likedItem = Item::factory()->create(['name' => 'Liked Item']);
        $user->favorites()->attach($likedItem->id);

        // いいねしていない商品
        Item::factory()->create(['name' => 'Not Liked Item']);

        // マイリストタブを表示
        $response = $this->get(route('auth.index', ['tab' => 'mylist']));

        $response->assertStatus(200);
        $response->assertSee('Liked Item');
        $response->assertDontSee('Not Liked Item');
    }

    /**
     * ID 5: マイリスト一覧取得 - 購入済みの場合はマイリストでも「SOLD」と表示される
     */
    public function test_sold_items_in_mylist_display_sold_label()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. 商品を作成して「いいね」する
        $item = Item::factory()->create(['name' => 'Sold Liked Item']);
        $user->favorites()->attach($item->id);

        // 2. その商品を「売り切れ」状態にする
        // ★修正: SoldItemモデルを使わず、buyer_idを更新する
        $buyer = User::factory()->create();
        $item->buyer_id = $buyer->id;
        $item->save();

        // 3. マイリストタブを表示
        $response = $this->get(route('auth.index', ['tab' => 'mylist']));

        $response->assertStatus(200);
        $response->assertSee('Sold Liked Item'); // 商品自体は表示される
        $response->assertSee('SOLD');            // SOLDラベルが表示される
    }

    /**
     * ID 5: マイリスト一覧取得 - ログインされていないユーザー（未認証）の場合は何も表示されない
     */
    public function test_guest_sees_nothing_in_mylist()
    {
        // 商品を作成しておく（ゲストには見えてはいけない商品）
        Item::factory()->create(['name' => 'Hidden Item']);

        // ★ actingAs($user) をしない（＝ゲスト状態）

        // マイリストタブを表示
        $response = $this->get(route('auth.index', ['tab' => 'mylist']));

        $response->assertStatus(200);
        
        // 商品が表示されていないことを確認
        $response->assertDontSee('Hidden Item');
    }

    /**
     * ID 9: コメント送信機能 - ログイン済みユーザーは送信できる
     */
    public function test_authenticated_user_can_post_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        $commentData = ['comment' => 'This is a test comment.'];

        // コメント投稿ルートへPOST
        $response = $this->post("/item/{$item->id}/comment", $commentData);

        $response->assertStatus(302); // リダイレクト

        // データベースに保存されているか
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'This is a test comment.',
        ]);

        // 詳細画面でコメント数が増えているか（表示確認）
        $response = $this->get("/item/{$item->id}");
        $response->assertSee('This is a test comment.');
    }

    /**
     * ID 9: コメント送信機能 - 未ログインユーザーは送信できない
     */
    public function test_guest_cannot_post_comment()
    {
        $item = Item::factory()->create();

        $response = $this->post("/item/{$item->id}/comment", [
            'comment' => 'Guest comment',
        ]);

        // ログイン画面へリダイレクト
        $response->assertRedirect('/login'); 
    }

    /**
     * ID 9: コメント送信機能 - バリデーション（空欄、文字数オーバー）
     */
    public function test_comment_validation()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // 空欄送信
        $response = $this->post("/item/{$item->id}/comment", ['comment' => '']);
        $response->assertSessionHasErrors(['comment']);

        // 256文字以上送信
        $longComment = str_repeat('a', 256);
        $response = $this->post("/item/{$item->id}/comment", ['comment' => $longComment]);
        $response->assertSessionHasErrors(['comment']);
    }
}