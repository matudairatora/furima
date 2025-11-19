<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\ShippingAddress;
use App\Models\SoldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ConditionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    /**
     * ID 10: 商品購入機能 - 購入処理が完了し、SoldItemに追加される
     */
    public function test_user_can_purchase_item()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($buyer);

        // コンビニ払いを選択して購入処理を実行
        $response = $this->post(route('item.process_purchase', ['itemId' => $item->id]), [
            'payment_method' => 'convenience',
            'user_address' => 'Test Address', 
        ]);

        // トップページ等へリダイレクト
        $response->assertRedirect(route('auth.index'));

        // sold_items テーブルにレコードがあるか
        $this->assertDatabaseHas('sold_items', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
        ]);

        // 商品一覧で「SOLD」表示になっているか
        $this->get('/')->assertSee('SOLD');
    }

    /**
     * ID 10: 商品購入機能 - 購入した商品がプロフィールの購入一覧に追加される
     */
    public function test_purchased_item_appears_in_profile()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        
        // 購入履歴作成（SoldItemを手動作成）
        SoldItem::create([
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // プロフィールの「購入した商品」タブへ
        $response = $this->get(route('auth.mypage', ['page' => 'buy']));

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    /**
     * ID 12: 配送先変更機能 - 変更した住所が購入画面に反映される
     */
    public function test_shipping_address_update_reflects_on_purchase_page()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // まず購入画面を表示（初期状態）
        $response = $this->get(route('item.purchase', ['itemId' => $item->id]));
        $response->assertStatus(200);

        // 配送先変更処理（住所を登録/更新）
        $newAddress = [
            'item_id' => $item->id,
            'postcode' => '999-9999',
            'address' => 'New Address City',
            'building' => 'New Building',
        ];

        $this->post(route('address.update'), $newAddress);

        // 再度購入画面を表示して、新しい住所が表示されているか確認
        $response = $this->get(route('item.purchase', ['itemId' => $item->id]));
        $response->assertSee('999-9999');
        $response->assertSee('New Address City');
    }
}