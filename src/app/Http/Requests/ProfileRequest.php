<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
         'name' =>['required', 'string','max:20'],
         'mypageimage' =>['image','mimes:jpeg,png'],
         'postcode' =>['required','regex:/^\d{3}-\d{4}$/'],
         'address' =>['required', 'string', 'max:255'],
        ];
    }

    public function messages()
     {
         return [
            'name.required'=>'ユーザー名を入力してください',
            'name.max'=>'20文字以内でお願いします',
            'mypageimage.mimes'=>'jpegかpngの画像をアップロードしてください',
            'postcode.required'=>'郵便番号を入力してください',
            'postcode.regex'=>'「-」を入れて入力してください',
            'address.required'=>'住所を入れてください'
         ];
     }
}
