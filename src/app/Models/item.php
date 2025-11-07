<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'category_id',
    ];

}
