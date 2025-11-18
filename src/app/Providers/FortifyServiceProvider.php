<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //  会員登録成功後のリダイレクト先
       $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('profile.edit');
            }
        });
        // メール認証成功後のリダイレクト先
        $this->app->instance(VerifyEmailResponse::class, new class implements VerifyEmailResponse {
            public function toResponse($request)
            {
                return redirect()->route('profile.edit');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
     Fortify::createUsersUsing(CreateNewUser::class);
     

     Fortify::registerView(function () {
         return view('auth.register');
     });

     Fortify::loginView(function () {
         return view('auth.login');
     });

     Fortify::verifyEmailView(function () {
            return view('auth.verify-email'); 
        });

     RateLimiter::for('login', function (Request $request) {
         $email = (string) $request->email;

         return Limit::perMinute(10)->by($email . $request->ip());
     }); 
     

    $this->app->bind(FortifyLoginRequest::class, RegisterRequest::class); 

    }
}
