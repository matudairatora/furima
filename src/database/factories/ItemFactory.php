<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
        'name' => $this->faker->word,
        'price' => $this->faker->numberBetween(100, 10000),
        'explanation' => $this->faker->sentence,
        'image' => 'test_image.jpg', // ダミー文字列
        'condition_id' => 1, // 固定値またはランダム
        'user_id' => \App\Models\User::factory(), // 自動でユーザーも作る
        'brand' => $this->faker->word,
        ];
    }
}
