<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;

class item extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',
        'image',
        'price',
        'brand',
        'explanation',
        'condition_id',
        'coment_id',
        'favorite_id',
        'user_id',
    ];
    public function categories()
    {
        // 中間テーブル名が 'item_category' の場合
        return $this->belongsToMany(Category::class); 
    }

    // 一対多の逆: 商品の状態
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }
    
    // 一対多の逆: 出品者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
