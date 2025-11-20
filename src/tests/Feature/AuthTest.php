<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase; // テスト実行ごとにデータベースをリセットする

    /**
     * ID 1: 会員登録機能 - 登録画面が表示される
     */
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * ID 1: 会員登録機能 - 正常に登録できる
     */
    public function test_new_users_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // 認証されていることを確認
        $this->assertAuthenticated();
        
        // データベースにユーザーが作成されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        
    }

    /**
     * ID 1: 会員登録機能 - バリデーションエラー（必須項目未入力）
     */
    public function test_registration_fails_validation_required_fields()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        // エラーメッセージがセッションに含まれているか確認
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /**
     * ID 1: 会員登録機能 - パスワード文字数不足＆不一致
     */
    public function test_registration_fails_password_mismatch()
{
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',       // 8文字以上 (OK)
        'password_confirmation' => 'diff_pass', // 不一致 (NG)
    ]);

    $response->assertSessionHasErrors(['password']);
}

public function test_registration_fails_password_length()
{
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',              // 7文字以下 (NG)
        'password_confirmation' => 'short', // 一致している (OK)
    ]);

    $response->assertSessionHasErrors(['password']);
}
    

    /**
     * ID 2: ログイン機能 - ログイン画面が表示される
     */
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * ID 2: ログイン機能 - 正しい情報でログインできる
     */
    public function test_users_can_authenticate_using_the_login_screen()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // 認証成功を確認
        $this->assertAuthenticated();
        // リダイレクト先を確認 (auth.indexへ遷移する設定の場合)
        $response->assertRedirect(route('auth.index'));
    }

    /**
     * ID 2: ログイン機能 - バリデーション（未入力）
     */
    public function test_login_fails_with_empty_fields()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * ID 2: ログイン機能 - 間違ったパスワードで失敗する
     */
    public function test_users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest(); // まだゲスト（未ログイン）であること
    }

    /**
     * ID 3: ログアウト機能
     */
    public function test_users_can_logout()
    {
        // ユーザー作成＆ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest(); // ログアウト後はゲスト状態であること
    }
}