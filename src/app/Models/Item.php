<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',
        'image',
        'price',
        'brand',
        'explanation',
        'condition_id',
        'user_id',
        'buyer_id',
    ];
    public function categories()
    {
        return $this->belongsToMany(Category::class); 
    }

    
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }
    
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments(): HasMany
    {
        
        return $this->hasMany(Comment::class);
    }

    public function favorites(): BelongsToMany
    {

        return $this->belongsToMany(User::class, 'favorites', 'item_id', 'user_id');
    }

      public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // 追加：売り切れかどうかを判定する便利メソッド（HTML表示などで役立ちます）
    public function isSold()
    {
        return $this->buyer_id !== null;
    }

}
