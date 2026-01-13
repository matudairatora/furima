<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\ShippingAddress;
// use App\Models\SoldItem; // ★削除
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
     * ID 10: 商品購入機能 - 購入処理が完了し、itemsテーブルのbuyer_idが更新される
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

        // ★修正: sold_items ではなく items テーブルの buyer_id を確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'buyer_id' => $buyer->id,
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
        
        // ★修正: 購入履歴作成（itemsテーブルのbuyer_idを更新）
        $item->buyer_id = $user->id;
        $item->save();

        $this->actingAs($user);

        // プロフィールの「購入した商品」タブへ
        $response = $this->get(route('auth.mypage', ['page' => 'buy']));

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    /**
     * ID 11: 支払い方法選択機能 - 「カード払い」を選択すると、Stripe決済画面へ遷移する
     */
    public function test_selecting_card_payment_redirects_to_stripe_checkout()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // カード払い ('card') を選択して送信
        $response = $this->post(route('item.process_purchase', ['itemId' => $item->id]), [
            'payment_method' => 'card',
            'user_address' => 'Test Address',
        ]);

        // Stripeのチェックアウト画面（またはカード情報入力画面）へリダイレクトされることを確認
        // ※ItemControllerの実装に合わせてリダイレクト先を確認してください
        $response->assertRedirect(route('checkout', ['itemId' => $item->id]));
    }

    /**
     * ID 11: 支払い方法選択機能 - 「コンビニ払い」を選択すると、即時購入完了する
     */
    public function test_selecting_convenience_payment_completes_purchase_immediately()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // コンビニ払い ('convenience') を選択して送信
        $response = $this->post(route('item.process_purchase', ['itemId' => $item->id]), [
            'payment_method' => 'convenience',
            'user_address' => 'Test Address',
        ]);

        // 購入完了後のトップページへリダイレクトされることを確認
        $response->assertRedirect(route('auth.index'));
        
        // ★修正: itemsテーブルのbuyer_idを確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'buyer_id' => $user->id,
        ]);
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

    /**
     * ID 12: 配送先変更機能 - 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_purchased_item_is_linked_to_shipping_address()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // 1. 配送先を変更（登録）する
        $newAddress = [
            'item_id' => $item->id,
            'postcode' => '888-8888',
            'address' => 'Linked City',
            'building' => 'Linked Building',
        ];

        $this->post(route('address.update'), $newAddress);

        // 2. 商品を購入する
        $this->post(route('item.process_purchase', ['itemId' => $item->id]), [
            'payment_method' => 'convenience',
            'user_address' => 'Linked City', 
        ]);

        // 3. データベース確認
        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'postcode' => '888-8888',
            'address' => 'Linked City',
            'building' => 'Linked Building',
        ]);
    }
}