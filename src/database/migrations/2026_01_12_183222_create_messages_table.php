<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 送信者
            $table->foreignId('item_id')->constrained()->cascadeOnDelete(); // 対象商品
            $table->string('content', 400)->nullable(); // 本文 (最大400文字 FN007)
            $table->string('image')->nullable(); // 画像パス
            $table->timestamp('read_at')->nullable(); // 既読日時
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
        Schema::dropIfExists('messages');
    }
}
