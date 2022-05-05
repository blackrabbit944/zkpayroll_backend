<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_profile', function (Blueprint $table) {
     
            $table->increments('id');

            $table->string('name',64)->nullable()->comment('用户名');
            $table->string('bio',256)->nullable()->comment('用户简介');
            $table->string('ens',256)->nullable()->comment('ENS地址');

            $table->integer('user_id')->default(0)->comment('用户ID');

            $table->integer('avatar_img_id')->nullable()->comment('头像');
            $table->integer('avatar_item_id')->nullable()->comment('NFT头像对应的ITEM_ID');

            $table->integer('create_time');
            $table->integer('update_time');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_profile');
    }
}
