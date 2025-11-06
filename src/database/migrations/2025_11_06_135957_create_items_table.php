<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->binary('image');
            $table->integer('price');
            $table->string('brand');
            $table->string('explanation');
            $table->integer('condition_id')->constrained()->cascadeOnDelete();
            $table->integer('coment_id')->constrained()->cascadeOnDelete()->nullable();
            $table->integer('favorite_id')->constrained()->cascadeOnDelete()->nullable();
            $table->integer('category_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('items');
    }
}
