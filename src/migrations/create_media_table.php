<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('content_id');
            $table->string('content_type');
            $table->string('name');
            $table->string('url');
            $table->string('path');
            $table->string('extension');
            $table->integer('size');
            $table->boolean('temp');
            $table->string('collection_name');
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
