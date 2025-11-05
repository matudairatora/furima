<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use App\Models\User;

class Mypage extends Model
{
    use HasFactory;

    protected $table = 'mypage'; 
    
    
    protected $fillable = [
        'user_id',
        'mypage',
        'postcode',
        'address',
        'building',
    ];

    /**
     * MypageはUserに属する
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
