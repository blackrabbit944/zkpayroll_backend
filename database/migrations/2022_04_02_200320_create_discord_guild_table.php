<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscordGuildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_discord_guild', function (Blueprint $table) {
     
            $table->increments('id');

            $table->integer('club_id')->nullable()->comment('所属的ClubId,可以为空');
            $table->string('discord_user_id',24)->comment('持有这个guild的discord_user_id');
            $table->string('guild_id',24)->unique()->comment('discord的GuildID');
            $table->string('icon',64)->nullable()->comment('icon_hash');
            $table->string('name',128)->nullable()->comment('name');

            $table->string('invite_code',32)->nullable()->comment('用于邀请URL其中code存储');

            $table->string('webhook_token',128)->nullable()->comment('用于发通知的webhook的token');
            $table->string('webhook_id',24)->nullable()->comment('用于发通知的webhook的id');

            $table->string('admin_webhook_token',128)->nullable()->comment('用于发ADMIN通知的webhook的token');
            $table->string('admin_webhook_id',24)->nullable()->comment('用于发ADMIN通知的webhook的id');

            $table->boolean('is_init')->default(0)->comment('是否已经初始化过');

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
        Schema::dropIfExists('b_discord_guild');
    }
}
