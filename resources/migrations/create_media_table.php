<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_type');
            $table->integer('model_id');
            $table->string('collection_name');
            $table->string('name');
            $table->string('file');
            $table->string('extension');
            $table->integer('size');
            $table->text('manipulations');
            $table->boolean('temp');
            $table->integer('order_column');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('media');
    }
}
