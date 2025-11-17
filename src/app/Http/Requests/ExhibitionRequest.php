<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => ['required'],
            'explanation'=>['required','max:255'],
            'image'=>['required','mimes:jpeg,png'],
            'categories' =>['required'],
            'condition' =>['required'],
            'price'=>['required','numeric','min:0'],
        ];
    }
    public function messages()
     {
         return [
            'name.required' => '商品名を入力してください',
            'explanation.required' => '商品説明を入力してください',
            'explanation.max'=>'255文字以内で入力してください',
            'image.required'=> '画像をアップロードしてください',
            'image.mimes'=>'jpegかpngの画像をアップロードしてください',
            'content.required'=>'カテゴリーを選択してください',
            'condition.required'=>'コンディションを入力してください',
            'price.required'=>'価格を入力してください',
            'price.numeric'=>'数値で入力してください',
            'price.min'=>'0円以上を入力してください',
         ];
     }
}
