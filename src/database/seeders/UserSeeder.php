<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '出品者A',
                'email' => 'user1@example.com',
                'password' => Hash::make('user1syuppin'), // パスワードは任意
                'email_verified_at' => now(),
            ],
            [
                'name' => '出品者B',
                'email' => 'user2@example.com',
                'password' => Hash::make('user2syuppin'),
                'email_verified_at' => now(),
            ],
            [
                'name' => '購入専用ユーザー',
                'email' => 'user3@example.com',
                'password' => Hash::make('user3kounyu'),
                'email_verified_at' => now(),
            ],
        ]);

        DB::table('mypage')->insert([
            [
                'user_id' => 1, // 出品者Aの住所
                'postcode' => '100-0001',
                'address' => '東京都千代田区千代田1-1',
                'building' => 'テックハイツ101',
                'mypage_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2, // 出品者Bの住所
                'postcode' => '530-0001',
                'address' => '大阪府大阪市北区梅田2-2-2',
                'building' => 'グランフロント大阪202',
                'mypage_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3, // 購入専用ユーザーの住所
                'postcode' => '810-0001',
                'address' => '福岡県福岡市中央区天神3-3-3',
                'building' => '博多ビル303',
                'mypage_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
