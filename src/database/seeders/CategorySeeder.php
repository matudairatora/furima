<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->truncate();
      
        $categories = [
            ['content' => 'ファッション'],
            ['content' => '電気'],
            ['content' => 'インテリア'],
            ['content' => '女性'],
            ['content' => 'メンズ'],
            ['content' => 'コスメ'],
            ['content' => '本'],
            ['content' => 'ゲーム'],
            ['content' => 'スポーツ'],
            ['content' => 'キッチン'],
            ['content' => 'ハンドメイド'],
            ['content' => '付属品'],
            ['content' => 'おもちゃ'],
            ['content' => 'ベビー・キッズ'],
        ];
      
        
        // categoriesテーブルにデータを挿入
        DB::table('categories')->insert($categories);
    }
}
