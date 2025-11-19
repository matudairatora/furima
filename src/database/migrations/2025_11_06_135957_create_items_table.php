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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('image');
            $table->integer('price');
            $table->string('brand')->nullable();
            $table->string('explanation');
            $table->integer('condition_id')->constrained()->cascadeOnDelete();
            $table->integer('coment_id')->constrained()->cascadeOnDelete()->nullable();
            $table->boolean('is_sold')->default(false); 
            $table->foreignId('buyer_id')->nullable()->constrained('users')->after('is_sold');

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
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropColumn('buyer_id');
            $table->dropColumn('is_sold');
        });
    }
}
