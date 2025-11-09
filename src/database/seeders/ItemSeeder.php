<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('items')->truncate();
      
        $items = [
        ['name' => '腕時計',
        'price' => '15000',
        'brand' => 'Rolax',
        'explanation' => 'スタイリッシュなデザインのメンズ腕時計',
        'condition_id' => 1,
        'image' => 'img/image/1.Armani+Mens+Clock.jpg',
        'user_id' => 1,
        ],
           ['name' => 'HDD',
        'price' => '5000',
        'brand' => '西芝',
        'explanation' => '高速で信頼性の高いハードディスク',
        'condition_id' => 2,
        'image' => 'img/image/2.HDD+Hard+Disk.jpg',
        'user_id' => 1,
        ],
        ['name' => '玉ねぎ３束',
        'price' => '300',
        'brand' => 'なし',
        'explanation' => '新鮮な玉ねぎ３束セット',
        'condition_id' => 3,
        'image' => 'img/image/3.iLoveIMG+d.jpg',
        'user_id' => 1,
        ],
        ['name' => '革靴',
        'price' => '4000',
        'brand' => '',
        'explanation' => 'クラシックなデザインの革靴',
        'condition_id' => 4,
        'image' => 'img/image/4.Leather+Shoes+Product+Photo.jpg',
        'user_id' => 1,
        ],
        ['name' => 'ノートＰＣ',
        'price' => '45000',
        'brand' => '',
        'explanation' => '高性能なノートパソコン',
        'condition_id' => 1,
        'image' => 'img/image/5.Living+Room+Laptop.jpg',
        'user_id' => 1,
        ],
        ['name' => 'マイク',
        'price' => '8000',
        'brand' => 'なし',
        'explanation' => '高音質のレコーディング用マイク',
        'condition_id' => 2,
        'image' => 'img/image/6.Music+Mic+4632231.jpg',
        'user_id' => 1,
        ],
        ['name' => 'ショルダーバッグ',
        'price' => '3500',
        'brand' => '',
        'explanation' => 'おしゃれなショルダーバッグ',
        'condition_id' => 3,
        'image' => 'img/image/7.Purse+fashion+pocket.jpg',
        'user_id' => 1,
        ],
        ['name' => 'タンブラー',
        'price' => '500',
        'brand' => 'なし',
        'explanation' => '使いやすいタンブラー',
        'condition_id' => 4,
        'image' => 'img/image/8.Tumbler+souvenir.jpg',
        'user_id' => 1,
        ],
        ['name' => 'コーヒーミル',
        'price' => '4000',
        'brand' => 'Starbacks',
        'explanation' => '手動のコーヒーミル',
        'condition_id' => 1,
        'image' => 'img/image/9.Waitress+with+Coffee+Grinder.jpg',
        'user_id' => 1,
        ],
        ['name' => 'メイクセット',
        'price' => '2500',
        'brand' => '',
        'explanation' => '便利なメイクアップセット',
        'condition_id' => 2,
        'image' => 'img/image/10.外出メイクアップセット.jpg',
        'user_id' => 1,
        ], 
        ];
      
        
        // categoriesテーブルにデータを挿入
        DB::table('items')->insert($items);
    }
}
