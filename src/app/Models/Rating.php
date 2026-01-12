<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',   // 評価される人
        'rater_id',  // 評価した人
        'item_id',   // 対象商品
        'rating',    // 星の数
    ];

    // リレーション定義 (必要に応じて利用)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
