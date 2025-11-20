<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト内容1: 会員登録後、認証メールが送信される
     */
    public function test_email_verification_mail_is_sent_upon_registration()
    {
        Notification::fake(); // メール送信をシミュレート（実際には送らない）

        // 会員登録処理を実行
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // VerifyEmail 通知（認証メール）が送られたことを確認
        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            VerifyEmail::class
        );
    }

    /**
     * テスト内容2: メール認証誘導画面が表示される（「認証はこちらから」ボタン等があるか）
     */
    public function test_email_verification_screen_can_be_rendered()
    {
        // メール未認証のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログイン状態で認証待ち画面（/email/verify）にアクセス
        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        
        // ボタンやリンクの文言が含まれているか確認
        // ※ verify-email.blade.php の内容に合わせています
        $response->assertSee('認証はこちらから'); 
        $response->assertSee('認証メールを再送信する');
    }

    /**
     * テスト内容3: メール認証サイトの認証を完了すると、プロフィール設定画面に遷移する
     */
    public function test_email_can_be_verified()
    {
        Event::fake(); // イベント発火をシミュレート

        // メール未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 正しい認証用URLを生成（Laravelの機能を使用）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 生成したURLにアクセス（＝メール内のリンクをクリックした挙動）
        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証完了イベントが発火したか確認
        Event::assertDispatched(Verified::class);

        // ユーザー情報の email_verified_at が更新されているか確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        
         $response->assertRedirect(route('profile.edit')); 
        
    }
}