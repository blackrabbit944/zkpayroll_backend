<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadImgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_upload_img', function (Blueprint $table) {
            $table->increments('img_id');
            $table->integer('user_id');
            $table->char('hash', 64);
            $table->char('require_hash', 16)->nullable();
            $table->string('require_data', 128)->nullable();
            $table->char('ip', 16);
            $table->string('file_type', 32);
            $table->enum('file_status', ['init', 'save', 'success'])->default('init');
            $table->enum('img_type', ['jpg', 'gif', 'png', 'webp', 'jpeg'])->default('jpg');
            $table->enum('original_img_type', ['jpg', 'gif', 'png', 'webp', 'jpeg'])->default('jpg');
            $table->integer('height')->default(0);
            $table->integer('width')->default(0);
            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('delete_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_upload_img');
    }
}
