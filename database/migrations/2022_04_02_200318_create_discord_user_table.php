<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscordUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_discord_user', function (Blueprint $table) {
     
            $table->increments('id');

            $table->string('discord_user_id',24)->nullable()->comment('discord上的用户id');
            $table->string('name',64)->nullable()->comment('discord用户名');
            $table->string('email',1024)->nullable()->comment('email地址');
            $table->string('discriminator',32)->nullable()->comment('discord生成唯一数字id');
            $table->string('avatar',32)->nullable()->comment('avatar_hash');

            $table->integer('user_id')->default(0)->comment('用户ID');

            $table->string('access_token',32)->nullable()->comment('access_token');
            $table->string('refresh_token',32)->nullable()->comment('refresh_token');
            $table->integer('token_expire_time')->default(0)->comment('token_expire_time');

            $table->integer('create_time');
            $table->integer('update_time')->nullable();
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
        Schema::dropIfExists('b_discord_user');
    }
}
