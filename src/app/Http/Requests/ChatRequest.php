<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストの権限を持っているか判断する
     * ここではチャット画面へのアクセス制限はControllerで行っているため true にします
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules()
    {
        return [
            'content' => 'required|max:400',
            'image' => 'nullable|image|mimes:jpeg,png',
        ];
    }

    /**
     * エラーメッセージのカスタマイズ
     */
    public function messages()
    {
        return [
            'content.required' => '本文を入力してください',
            'content.max' => '本文は400文字以内で入力してください',
            'image.image' => '「.png」または「.jpeg」形式でアップロードしてください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}