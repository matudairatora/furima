<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();
        \App\Models\Condition::create(['id' => 1, 'condition' => '良好','content' => 'ファッション']);
        
    }

    /**
     * ID 13: ユーザー情報取得 - プロフィール画面に必要な情報が表示される
     */
    public function test_profile_page_displays_user_info()
    {
        $user = User::factory()->create(['name' => 'Profile Test User']);
        
        // プロフィール情報（mypage）を作成
        $user->mypage()->create([
            'postcode' => '123-4567',
            'address' => 'Test Address',
            'building' => 'Test Building',
        ]);

        // 出品商品を作成
        Item::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Selling Item',
            'condition_id' => 1, 
        ]);

        $this->actingAs($user);

        // マイページ（出品した商品タブ）へ
        $response = $this->get(route('auth.mypage', ['page' => 'sell']));

        $response->assertStatus(200);
        $response->assertSee('Profile Test User'); // 名前
        $response->assertSee('My Selling Item');   // 出品商品
        
    }

    /**
     * ID 14: ユーザー情報変更 - 変更項目が初期値として表示されている
     */
    public function test_profile_edit_page_displays_current_info()
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $user->mypage()->create([
            'postcode' => '000-0000',
            'address' => 'Old Address',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        // inputタグのvalueに値が入っているか確認
        $response->assertSee('value="Old Name"', false);
        $response->assertSee('value="000-0000"', false);
        $response->assertSee('value="Old Address"', false);
    }

    /**
     * ID 14: ユーザー情報変更 - プロフィールを更新できる
     */
    public function test_user_can_update_profile()
    {
        Storage::fake('public'); // 画像保存のフェイク

        $user = User::factory()->create();
        $this->actingAs($user);

        $image = UploadedFile::fake()->create('profile.jpg');

        $updateData = [
            'name' => 'New Name',
            'postcode' => '111-1111',
            'address' => 'New Address',
            'building' => 'New Building',
            'mypageimage' => $image,
        ];

        // 更新処理へPOST
        $response = $this->post(route('profile.update'), $updateData);

        $response->assertRedirect(route('auth.index')); // 完了後のリダイレクト

        // データベースが更新されているか確認
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
        $this->assertDatabaseHas('mypage', [
            'user_id' => $user->id,
            'postcode' => '111-1111',
            'address' => 'New Address',
        ]);
    }
}