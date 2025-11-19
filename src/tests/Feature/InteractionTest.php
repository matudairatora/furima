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