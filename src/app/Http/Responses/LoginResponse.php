<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
{
    /**
     * ログインが成功した後のHTTPレスポンスを作成します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // ユーザーの profile_setup_completed フラグをチェック
        if ($user && $user->profile_setup_completed === false) {
            // プロフィールが未完了の場合、profile.edit ルートにリダイレクト
            return redirect()->intended(route('profile.edit'));
        }

        // プロフィールが完了している場合、またはその他の場合は、
        // Fortifyのデフォルトホーム（config/fortify.php の 'home'）にリダイレクト
        return redirect()->intended(config('fortify.home'));
    }
}