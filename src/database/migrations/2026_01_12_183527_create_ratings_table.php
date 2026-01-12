<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 評価された人
        $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete(); // 評価した人
        $table->foreignId('item_id')->constrained()->cascadeOnDelete(); // 対象商品
        $table->unsignedTinyInteger('rating'); // 1~5の星の数
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
