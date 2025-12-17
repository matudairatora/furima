<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('conditions')->truncate();
      
        $conditions = [
            ['condition' => '良好'],
            ['condition' => '目立った傷や汚れなし'],
            ['condition' => 'やや傷や汚れあり'],
            ['condition' => '状態が悪い'],
        ];
      
        
        // categoriesテーブルにデータを挿入
        DB::table('conditions')->insert($conditions);
    }
}
