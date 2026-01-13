<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト前の準備（コンディションとカテゴリーの作成）
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Item作成に必要なマスターデータを準備
        if (Condition::count() === 0) {
            Condition::create(['id' => 1, 'condition' => '良好']);
            Condition::create(['id' => 2, 'condition' => '傷あり']);
        }
        if (Category::count() === 0) {
            Category::create(['id' => 1, 'content' => 'ファッション']);
            Category::create(['id' => 2, 'content' => '家電']);
        }
    }

    /**
     * ID 4: 商品一覧取得 - 全商品を取得できる
     */
    public function test_can_see_item_list()
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 商品作成
        Item::create([
            'user_id' => $user->id,
            'condition_id' => 1,
            'name' => 'Test Item A',
            'price' => 1000,
            'explanation' => 'Description A',
            'image' => 'test.jpg',
            'brand' => 'Brand A',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Item A');
    }

    /**
     * ID 4: 商品一覧取得 - 購入済み商品は「Sold」と表示される
     */
    public function test_sold_items_display_sold_label()
    {
        $user = User::factory()->create();
        $buyer = User::factory()->create();

        // 商品作成
        $item = Item::create([
            'user_id' => $user->id,
            'condition_id' => 1,
            'name' => 'Sold Item',
            'price' => 2000,
            'explanation' => 'Sold Description',
            'image' => 'sold.jpg',
        ]);

        // ★修正: SoldItemモデルを使わず、itemsテーブルのbuyer_idを更新して「売り切れ」にする
        $item->buyer_id = $buyer->id;
        $item->save();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sold Item');
        $response->assertSee('SOLD'); // SOLDラベルのテキスト確認
    }

    /**
     * ID 4: 商品一覧取得 - 自分が出品した商品は表示されない
     */
    public function test_own_items_are_not_displayed_in_list()
    {
        // ユーザー作成＆ログイン
        $me = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($me);

        // 自分の商品
        Item::create([
            'user_id' => $me->id,
            'condition_id' => 1,
            'name' => 'My Item',
            'price' => 3000,
            'explanation' => 'My Description',
            'image' => 'my.jpg',
        ]);

        // 他人の商品
        Item::create([
            'user_id' => $other->id,
            'condition_id' => 1,
            'name' => 'Other Item',
            'price' => 4000,
            'explanation' => 'Other Description',
            'image' => 'other.jpg',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('My Item'); // 自分の商品は見えない
        $response->assertSee('Other Item');  // 他人の商品は見える
    }

    /**
     * ID 6: 商品検索機能 - 「商品名」で部分一致検索ができる
     */
    public function test_can_search_items_by_name_partial_match()
    {
        $user = User::factory()->create();

        // 検索対象の商品
        Item::create([
            'user_id' => $user->id,
            'condition_id' => 1,
            'name' => 'Apple Watch',
            'price' => 50000,
            'explanation' => 'Watch',
            'image' => 'watch.jpg',
        ]);

        // 検索対象外の商品
        Item::create([
            'user_id' => $user->id,
            'condition_id' => 1,
            'name' => 'Orange Juice',
            'price' => 100,
            'explanation' => 'Juice',
            'image' => 'orange.jpg',
        ]);

        // "Apple" で検索
        $response = $this->get('/?keyword=Apple');

        $response->assertStatus(200);
        $response->assertSee('Apple Watch');
        $response->assertDontSee('Orange Juice');
    }

    /**
     * ID 7: 商品詳細情報取得 - 必要な情報が表示される
     */
    public function test_can_see_item_detail_with_category()
    {
        $user = User::factory()->create();

        // 商品作成
        $item = Item::create([
            'user_id' => $user->id,
            'condition_id' => 1, // 良好
            'name' => 'Detail Test Item',
            'price' => 12345,
            'brand' => 'TestBrand',
            'explanation' => 'This is a detail test.',
            'image' => 'detail.jpg',
        ]);

        // カテゴリーを紐付け
        $item->categories()->attach([1, 2]); // ファッション

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('Detail Test Item');
        $response->assertSee('12,345'); // 金額フォーマット
        $response->assertSee('TestBrand');
        $response->assertSee('This is a detail test.');
        $response->assertSee('良好'); // コンディション名

        $response->assertSee('ファッション'); 
        $response->assertSee('家電');
    }

    /**
     * ID 15: 出品商品情報登録 - 商品を出品できる
     */
    public function test_can_create_new_item()
    {
        Storage::fake('public'); // 画像保存のフェイク（実際には保存しない）
        
        $user = User::factory()->create();
        $this->actingAs($user); // ログイン状態

        $image = UploadedFile::fake()->create('item.jpg'); // ダミー画像

        $data = [
            'name' => 'New Exhibition Item',
            'brand' => 'New Brand',
            'explanation' => 'This is a new item.',
            'price' => 9800,
            'condition' => 1,
            'categories' => [1, 2], 
            'image' => $image,
        ];

        // 出品処理へのPOSTリクエスト
        $response = $this->post(route('item.create'), $data);

        // エラーがないか確認（リダイレクトされるはず）
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('auth.index')); // 完了後のリダイレクト先

        // データベースに保存されているか確認
        $this->assertDatabaseHas('items', [
            'name' => 'New Exhibition Item',
            'brand' => 'New Brand',
            'price' => 9800,
            'user_id' => $user->id,
        ]);

        // カテゴリーの中間テーブルも確認
        $item = Item::where('name', 'New Exhibition Item')->first();
        $this->assertTrue($item->categories->contains(1));
        $this->assertTrue($item->categories->contains(2));
    }
}