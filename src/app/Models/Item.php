<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Category;

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
    
    public function soldItem()
    {
        return $this->hasOne(SoldItem::class);
    }

        public function isSold()
    {
        return $this->soldItem !== null;
    }

}
